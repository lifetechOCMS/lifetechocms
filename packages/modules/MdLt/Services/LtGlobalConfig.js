<script>
 
const ltTokenName = "<?= ApiTokenDetails::tokenName(); ?>"; 
    // Global Server & API Configuration
window.LT_CONFIG = window.LT_CONFIG || {
  baseUrl: "<?= ApiTokenDetails::backEndBaseUrl(); ?>",
  siteHostAddress: "<?= ltSiteHostAddress() ?>",
  token: localStorage.getItem(`${ltTokenName}`)
};
    
</script>