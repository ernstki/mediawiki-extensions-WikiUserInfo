<?php
# source: https://www.mediawiki.org/wiki/Extension:WikiUserInfo

# Not a valid entry point, skip unless MEDIAWIKI is defined
if ( !defined( 'MEDIAWIKI' ) ) {
    exit( 1 );
}

$wgExtensionCredits['other'][] = array(
    "name" => "WikiUserInfo",
    "author" => "Michael P. Dubner",
    "version" => "0.5.1",
    "url" => "https://www.mediawiki.org/wiki/Extension:WikiUserInfo",
    "description" => "Allows to get info and options of user specified by name"
);

$wgExtensionFunctions[] = "wfWikiUserInfoExtension";
$wgHooks['LanguageGetMagic'][] = array( "wfWikiUserInfoMagicWords" );
$wgWikiUserInfoSafeOptions = array( 'date', 'gender', 'language', 'nickname', 'skin', 'timecorrection' );
$wgAvailableRights[] = 'showuseroption';

if ( !array_key_exists( 'showuseroption', $wgGroupPermissions['*'] ) ) {
    $wgGroupPermissions['*']['showuseroption'] = false;
    $wgGroupPermissions['sysop']['showuseroption'] = true;
    $wgGroupPermissions['bureaucrat']['showuseroption'] = true;
}

$wgAvailableRights[] = 'showuseremail';

if ( !array_key_exists( 'showuseremail', $wgGroupPermissions['*'] ) ) {
    $wgGroupPermissions['*']['showuseremail'] = false;
    $wgGroupPermissions['sysop']['showuseremail'] = true;
    $wgGroupPermissions['bureaucrat']['showuseremail'] = true;
}

function wfWikiUserInfoExtension() {
    global $wgParser;
    WikiUserInfo_MediaWiki::registerHooks( $wgParser );
}

function wfWikiUserInfoMagicWords( &$magicWords, $langCode ) {
    return WikiUserInfo_MediaWiki::addMagicWord( $magicWords, $langCode );
}

class WikiUserInfo_MediaWiki {

    static function registerHooks( $parser ) {
        $parser->setFunctionHook( 'realname', array( __CLASS__, "realname" ) );
        $parser->setFunctionHook( 'email', array( __CLASS__, "email" ) );
        $parser->setFunctionHook( 'nickname', array( __CLASS__, "nickname" ) );
        $parser->setFunctionHook( 'useroption', array( __CLASS__, "useroption" ) );
        $parser->setFunctionHook( 'userregistration', array( __CLASS__, "userregistration" ) );
        $parser->setFunctionHook( 'usergroups', array( __CLASS__, "usergroups" ) );
        $parser->setFunctionHook( 'useredits', array( __CLASS__, "useredits" ) );
    }

    static function addMagicWord( &$magicWords, $langCode ) {
        $magicWords['realname'] = array( 0, 'realname' );
        $magicWords['email'] = array( 0, 'email' );
        $magicWords['nickname'] = array( 0, 'nickname' );
        $magicWords['useroption'] = array( 0, 'useroption' );
        $magicWords['userregistration'] = array( 0, 'userregistration' );
        $magicWords['usergroups'] = array( 0, 'usergroups' );
        $magicWords['useredits'] = array( 0, 'useredits' );
        return true;
    }

    static function getUser( $parser, $user ) {
        $title = Title::newFromText( $user );
        if ( is_object( $title ) && $title->getNamespace() == NS_USER ) $user = $title->getText();
        $user = User::newFromName( $user );
        if ( !$user ) {
            global $wgUser;
            $user = $wgUser;
        }
        return $user;
    }

    static function realname( $parser, $user ) {
        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
        if ( !$user->getRealName() ) return $user->getName();
        return $user->getRealName();
    }

    static function email( $parser, $user ) {
        global $wgUser, $wgOut;
        if ( !$wgUser->isAllowed( 'showuseremail' ) && !$wgUser->isAllowed( 'lookupuser' ) ) {
            $wgOut->permissionRequired( 'showuseremail' );
            return;
        }
        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
        return $user->getEmail();
    }

    static function nickname( $parser, $user ) {
        return WikiUserInfo_MediaWiki::useroption( $parser, $user, 'nickname' );
    }

    static function useroption( $parser, $user, $option ) {
        global $wgUser, $wgOut;
        if ( !in_array( $option, $wgWikiUserInfoSafeOptions ) && !$wgUser->isAllowed( 'showuseroption' ) &&
            !$wgUser->isAllowed( 'lookupuser' ) ) {
            $wgOut->permissionRequired( 'showuseroption' );
            return;
        }
        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
        return $user->getOption( $option );
    }

    static function userregistration( $parser, $user ) {
        global $wgUser, $wgOut;
        if ( !$wgUser->isAllowed( 'showuseroption' ) && !$wgUser->isAllowed( 'lookupuser' ) ) {
            $wgOut->permissionRequired( 'showuseroption' );
            return;
        }
        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
        return $user->getRegistration();
    }

    static function usergroups( $parser, $user ) {
        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
        return join( ',', $user->getGroups() );
    }

    static function useredits( $parser, $user ) {
        $user = WikiUserInfo_MediaWiki::getUser( $parser, $user );
        return $user->getEditCount();
    }
}
