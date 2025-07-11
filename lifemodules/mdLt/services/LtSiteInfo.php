<?php
class LtSiteInfo extends LtModel{
private $tableName = 'SiteTb';



}


/*
* @creating helper fuction for site information
*
*
*/
function getSiteInfo(){
    $info = new LtSiteInfo();
    $load = $info->get()[0];
    return $load;
}
function ltSiteDbName(){
   return getSiteInfo()->dbName; 
}
function ltSiteName(){
   return getSiteInfo()->siteName; 
}
function ltSiteTitle(){
   return getSiteInfo()->subtit; 
}
function ltSiteAlias(){
   return getSiteInfo()->siteAlias; 
}
function ltSiteCopyright(){
   return getSiteInfo()->siteCopyright; 
}
function ltSiteEmail(){
   return getSiteInfo()->emailAdd; 
}
function ltSiteEmail2(){
   return getSiteInfo()->emailAdd2; 
}
function ltSitePhone(){
   return getSiteInfo()->phoneNo; 
}
function ltSitePhone2(){
   return getSiteInfo()->phoneNo2; 
}
function ltSiteAddress(){
   return getSiteInfo()->address1; 
}
function ltSiteAddress2(){
   return getSiteInfo()->address2; 
}
function ltSiteLogo(){
   return getSiteInfo()->logo; 
}
function ltSiteFavicon(){
   return getSiteInfo()->favicon; 
}
function ltSiteOpenHour(){
   return getSiteInfo()->openHour; 
}
function ltSiteClosedHour(){
   return getSiteInfo()->closedHour; 
}
function ltSiteLinkedln(){
   return getSiteInfo()->linkedln; 
}
function ltSiteInstagram(){
   return getSiteInfo()->instagram; 
}
function ltSiteFacebook(){
   return getSiteInfo()->facebook; 
}
function ltSiteTwitter(){
   return getSiteInfo()->twitter; 
}
function ltSiteYoutube(){
   return getSiteInfo()->youtube; 
}
function ltSiteHostAddress(){
   return getSiteInfo()->siteHostAddress; 
} 
?>