<?php
namespace Lt\Modules\MdLt\Services;


class LtSiteInfo extends \LtModel
{
    protected $tableName = 'TbSite';
    
    public static function getSiteInfo()
    {
        $info = new self();
        $load = $info->get()[0] ?? null;
        return $load;
    }
    
    public static function dbName()
    {
        return self::getSiteInfo()?->dbName;
    }
    
    public static function siteName()
    {
        return self::getSiteInfo()?->siteName;
    }
    
    public static function siteTitle()
    {
        return self::getSiteInfo()?->subtit;
    }
    public static function pageTitle()
    {
        return self::getSiteInfo()?->pageTitle;
    }
    
    public static function siteAlias()
    {
        return self::getSiteInfo()?->siteAlias;
    }
    
    public static function siteCopyright()
    {
        return self::getSiteInfo()?->siteCopyright;
    }
    
    public static function siteEmail()
    {
        return self::getSiteInfo()?->emailAdd;
    }
    
    public static function siteEmail2()
    {
        return self::getSiteInfo()?->emailAdd2;
    }
    
    public static function sitePhone()
    {
        return self::getSiteInfo()?->phoneNo;
    }
    
    public static function sitePhone2()
    {
        return self::getSiteInfo()?->phoneNo2;
    }
    
    public static function siteAddress()
    {
        return self::getSiteInfo()?->address1;
    }
    
    public static function siteAddress2()
    {
        return self::getSiteInfo()?->address2;
    }
    
    public static function siteLogo()
    {
        return self::getSiteInfo()?->logo;
    }
    
    public static function siteFavicon()
    {
        return self::getSiteInfo()?->favicon;
    }
    
    public static function siteOpenHour()
    {
        return self::getSiteInfo()?->openHour;
    }
    
    public static function siteClosedHour()
    {
        return self::getSiteInfo()?->closedHour;
    }
    
    public static function siteLinkedln()
    {
        return self::getSiteInfo()?->linkedln;
    }
    
    public static function siteInstagram()
    {
        return self::getSiteInfo()?->instagram;
    }
    
    public static function siteFacebook()
    {
        return self::getSiteInfo()?->facebook;
    }
    
    public static function siteTwitter()
    {
        return self::getSiteInfo()?->twitter;
    }
    
    public static function siteYoutube()
    {
        return self::getSiteInfo()?->youtube;
    }
    
    public static function siteHostAddress()
    {
        $value = self::getSiteInfo()?->siteHostAddress ?? '';
        return rtrim($value, '/');
    }
    
    public static function isHttps()
    {
    return (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
    (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ||
    (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false)
    );
    }
    
    public static function domain()
    {
        return $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    }
    
    public static function baseUrl()
    {
        return (self::isHttps() ? 'https://' : 'http://') . self::domain();
    }
    
    public static function siteFaviconUrl()
    {
        $getfaviconUrl = self::siteUrl()."/storage/media/".self::siteFavicon();
        return $getfaviconUrl;
    }
    
    public static function siteLogoUrl()
    {
        $getLogoUrl = self::siteUrl()."/storage/media/".self::siteLogo();
        return $getLogoUrl;
    }
    
    public static function siteUrl()
    {
        return self::baseUrl() . self::siteHostAddress();
    }
    
    public static function themePath()
    {
        return self::baseUrl() . self::siteHostAddress() . '/packages/themes';
    }
    
    public static function modulePath()
    {
        return self::baseUrl() . self::siteHostAddress() . '/packages/modules';
    }
}