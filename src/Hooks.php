<?php
# source: https://www.mediawiki.org/wiki/Extension:WikiUserInfo

namespace MediaWiki\Extension\WikiUserInfo;

use MediaWiki\MediaWikiServices;
use MWException;
use Parser;
use PermissionsError;
use Title;
use User;

// deprecated in 1.16.0; removed in 1.33.0
#$wgHooks['LanguageGetMagic'][] = array( "wfWikiUserInfoMagicWords" );
$wgWikiUserInfoSafeOptions = array( 'date', 'gender', 'language', 'nickname', 'skin', 'timecorrection' );

class Hooks {
    // use extension.json + ExtensionMessagesFiles now
//    function wfWikiUserInfoMagicWords( &$magicWords, $langCode ) {
//        return WikiUserInfo_MediaWiki::addMagicWord( $magicWords, $langCode );
//    }

    // ref: https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
    /**
     * @param Parser $parser
     * @throws MWException
     */
    public static function onParserFirstCallInit(Parser $parser ) {
        // since 1.25, can put these in extension.json
        // ref: https://www.mediawiki.org/wiki/Manual:$wgAvailableRights
        //$wgAvailableRights[] = 'showuseroption';
        //if ( !array_key_exists( 'showuseroption', $wgGroupPermissions['*'] ) ) {
        //    $wgGroupPermissions['*']['showuseroption'] = false;
        //    $wgGroupPermissions['sysop']['showuseroption'] = true;
        //    $wgGroupPermissions['bureaucrat']['showuseroption'] = true;
        //}
        //$wgAvailableRights[] = 'showuseremail';
        //if ( !array_key_exists( 'showuseremail', $wgGroupPermissions['*'] ) ) {
        //    $wgGroupPermissions['*']['showuseremail'] = false;
        //    $wgGroupPermissions['sysop']['showuseremail'] = true;
        //    $wgGroupPermissions['bureaucrat']['showuseremail'] = true;
        //}

        // for later...
        //$wgWikiUserInfoSafeOptions = ['date', 'gender', 'language', 'nickname', 'skin', 'timecorrection'];
        #$wgWikiUserInfoSafeOptions = ['nickname'];

        // ref: https://www.mediawiki.org/wiki/Manual:Parser_functions#The_setFunctionHook_hook
        $parser->setFunctionHook( 'realname', [ self::class, "realname" ] );
        $parser->setFunctionHook( 'email', [ self::class, "email" ] );
        $parser->setFunctionHook( 'nickname', [ self::class, "nickname" ] );

//        $parser->setFunctionHook( 'email', array( __CLASS__, "email" ) );
//        $parser->setFunctionHook( 'useroption', array( __CLASS__, "useroption" ) );
//        $parser->setFunctionHook( 'userregistration', array( __CLASS__, "userregistration" ) );
//        $parser->setFunctionHook( 'usergroups', array( __CLASS__, "usergroups" ) );
//        $parser->setFunctionHook( 'useredits', array( __CLASS__, "useredits" ) );
    } // self::onParserFirstCallInit

    // do this in WikiUserInfo.i18n.magic.php now (?)
//    static function addMagicWord( &$magicWords, $langCode ) {
//        $magicWords['realname'] = array( 0, 'realname' );
//        $magicWords['email'] = array( 0, 'email' );
//        $magicWords['nickname'] = array( 0, 'nickname' );
//        $magicWords['useroption'] = array( 0, 'useroption' );
//        $magicWords['userregistration'] = array( 0, 'userregistration' );
//        $magicWords['usergroups'] = array( 0, 'usergroups' );
//        $magicWords['useredits'] = array( 0, 'useredits' );
//        return true;
//    }

    /**
     * Function to handle the (one and only) argument from the {{#realname:...}},
     * et al. parser functions and turn it into a real live User object
     *
     * Accepts something like "User:Username" _or_ just plain "Username";
     * (FIXME?) defaults to $wgUser as a fallback
     *
     * @param Parser $parser
     * @param $user
     * @return string|User
     */
    private static function getUser(Parser $parser, $user ) {
        $title = Title::newFromText( $user );
        if ( is_object( $title ) && $title->inNamespace(NS_USER) ) {
            // strips "User:" from "User:Username"?
            $user = $title->getText();
        }
        $user = User::newFromName( $user );

        // fall back to $wgUser, which "encapsulates the state of the user viewing/using the site"
        // according to https://www.mediawiki.org/wiki/Manual:$wgUser:
        //   "In most cases $wgUser should not be used in new code."
        if ( !$user ) {
            global $wgUser;
            $user = $wgUser;
        }

        return $user;
    } // self::getUser

    static function realname( $parser, $user ) {
        $user = self::getUser( $parser, $user );
        if ( !$user->getRealName() ) return $user->getName();
        return $user->getRealName();
    }

    /**
     * @param $parser
     * @param $user
     * @return string
     * @throws PermissionsError
     */
    static function email($parser, $user ) {
        //global $wgUser, $wgOut;
        // $wgUser->isAllowed is deprecated since 1.34
        //  use MediaWikiServices::getInstance()->getPermissionManager()->userHasRight(...) instead
//        if ( !$wgUser->isAllowed( 'showuseremail' ) && !$wgUser->isAllowed( 'lookupuser' ) ) {
//            $wgOut->permissionRequired( 'showuseremail' );
//            return;
//        }
        $user = self::getUser( $parser, $user );

        // FIXME: figure out how to initialize *once*, in constructor
        $pmgr = MediaWikiServices::getInstance()->getPermissionManager();

        // presumably, 'lookupuser' comes from https://www.mediawiki.org/wiki/Extension:LookupUser
        //if ( !$pmgr->userHasRight( $user, 'showuseremail' ) && !$pmgr->userHasRight( $user, 'lookupuser' ) ) {
        if ( !$pmgr->userHasRight( $user, 'showuseremail' ) ) {
            // deprecated in 1.18; removed in 1.27: //$wgOut->permissionRequired( 'showuseremail' );
            // see https://github.com/wikimedia/mediawiki/commit/dc0dd31776704e46e892a09ba6e8c60c3b4d10cf
            throw new PermissionsError( 'showuseremail' );
        }
        return $user->getEmail();
    }

    static function nickname( $parser, $user ) {
        return self::useroption( $parser, $user, 'nickname' );
    }

    static function useroption( $parser, $user, $option ) {
        global $wgWikiUserInfoSafeOptions;
        // FIXME: figure out how to initialize *once*, in constructor
        $user = self::getUser( $parser, $user );
        $pmgr = MediaWikiServices::getInstance()->getPermissionManager();

        if ( //!in_array( $option, $wgWikiUserInfoSafeOptions ) or
             !$pmgr->userHasRight( $user, 'showuseroption' ) )
             /* or !$pmgr->userHasRight( $user, 'lookupuser' ) ) */
        {
            throw new PermissionsError( 'showuseroption' );
            //return;
        }
        return $user->getOption( $option );
    }

//    static function userregistration( $parser, $user ) {
//        global $wgUser, $wgOut;
//        if ( !$wgUser->isAllowed( 'showuseroption' ) && !$wgUser->isAllowed( 'lookupuser' ) ) {
//            $wgOut->permissionRequired( 'showuseroption' );
//            return;
//        }
//        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
//        return $user->getRegistration();
//    }
//
//    static function usergroups( $parser, $user ) {
//        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
//        return join( ',', $user->getGroups() );
//    }
//
//    static function useredits( $parser, $user ) {
//        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
//        return $user->getEditCount();
//    }

} // class Hooks
