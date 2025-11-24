<?php
# original source: https://www.mediawiki.org/wiki/Extension:WikiUserInfo
namespace MediaWiki\Extension\WikiUserInfo;

use MediaWiki\MediaWikiServices;
use User;
use Title;
use Parser;
use RequestContext;
use PermissionsError;

global $wgWikiUserInfoSafeOptions;

/** user options you're OK with your site's users being able to access */
$wgWikiUserInfoSafeOptions ??= [ 'nickname' ];
// supported now: 'realname', 'email'
// later: ['date', 'gender', 'language', 'skin', 'timecorrection', 'groups', …]

class Hooks {

    // ref: https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
    /**
     * @param Parser $parser
     */
    public static function onParserFirstCallInit( Parser $parser ) {
        // ref: https://www.mediawiki.org/wiki/Manual:Parser_functions#The_setFunctionHook_hook
        $parser->setFunctionHook( 'realname', [ self::class, "realname" ] );
        $parser->setFunctionHook( 'email', [ self::class, "email" ] );
        $parser->setFunctionHook( 'nickname', [ self::class, "nickname" ] );
        //$parser->setFunctionHook( 'useroption', [ self::class "useroption" ] );
        //$parser->setFunctionHook( 'userregistration', [ self::class, "userregistration" ] );
        //$parser->setFunctionHook( 'usergroups', [ self::class, "usergroups" ] );
        //$parser->setFunctionHook( 'useredits', [ self::class, "useredits" ] );
    }

    /**
     * Function to handle the (one and only) argument from the {{#realname:...}},
     * et al. parser functions and turn it into a real live User object
     *
     * Accepts something like "User:Username" _or_ just plain "Username";
     *
     * @param Parser $parser
     * @param $user
     * @return string|User
     */
    private static function getUser( Parser $parser, $user ) {
        $title = Title::newFromText( $user );
        if ( is_object( $title ) && $title->inNamespace(NS_USER) ) {
            $user = $title->getText();  // strips "User:" from "User:Username"
        }
        $user = User::newFromName( $user );
        return $user;
    }

    /**
     * Get a User object for the currently-logged-in user
	 * @return User
	 */
	private static function getCurrentUser() {
		return RequestContext::getMain()->getUser();
	}

    /**
     * Return the real name for the given user
     * @param Parser $parser
     * @param $user
     * @return string
     */
    static function realname( Parser $parser, $user ) {
        global $wgWikiUserInfoSafeOptions;
        $pmgr = MediaWikiServices::getInstance()->getPermissionManager();
        $curUser = self::getCurrentUser();

        if ( !in_array( 'realname', $wgWikiUserInfoSafeOptions ) or
             !$pmgr->userHasRight( $curUser, 'showuseroption' ) )
        {
            throw new PermissionsError( 'showuseroption' );
        }

        $user = self::getUser( $parser, $user );
        if ( !$user->getRealName() ) return $user->getName();
        return $user->getRealName();
    }

    /**
     * Return the email for the given user
     * @param Parser $parser
     * @param $user
     * @return string
     * @throws PermissionsError
     */
    static function email( $parser, $user ) {
        $pmgr = MediaWikiServices::getInstance()->getPermissionManager();
        $curUser = self::getCurrentUser();
        if ( !$pmgr->userHasRight( $curUser, 'showuseremail' ) ) {
            throw new PermissionsError( 'showuseremail' );
        }
        $user = self::getUser( $parser, $user );
        return $user->getEmail();
    }

    /**
     * Return the nickname (sig) for the given user
     * @param Parser $parser
     * @param $user
     * @return string
     */
    static function nickname( $parser, $user ) {
        return self::useroption( $parser, $user, 'nickname' );
    }

    /**
     * Return an arbitrary user option by its name, for the given user
     * @param $parser
     * @param $user
     * @param $option
     * @return string
     * @throws PermissionsError
     */
    static function useroption( $parser, $user, $option ) {
        global $wgWikiUserInfoSafeOptions;
        $pmgr = MediaWikiServices::getInstance()->getPermissionManager();
        $curUser = self::getCurrentUser();

        if ( !in_array( $option, $wgWikiUserInfoSafeOptions ) or
             !$pmgr->userHasRight( $curUser, 'showuseroption' ) )
        {
            throw new PermissionsError( 'showuseroption' );
        }

        $user = self::getUser( $parser, $user );
        // FIXME (1.35): supposed to use UserOptionsLookup::getOption instead
        return $user->getOption( $option );
    }

    //static function userregistration( $parser, $user ) {
    //    global $wgUser, $wgOut;
    //    if ( !$wgUser->isAllowed( 'showuseroption' )
    //         && !$wgUser->isAllowed( 'lookupuser' ) )
    //    {
    //        $wgOut->permissionRequired( 'showuseroption' );
    //        return;
    //    }
    //    $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
    //    return $user->getRegistration();
    //}
    //
    //static function usergroups( $parser, $user ) {
    //    $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
    //    return join( ',', $user->getGroups() );
    //}
    //
    //static function useredits( $parser, $user ) {
    //    $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
    //    return $user->getEditCount();
    //}

} // class Hooks
