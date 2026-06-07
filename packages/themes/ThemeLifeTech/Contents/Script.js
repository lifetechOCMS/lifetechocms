<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    const BASE_URL = "<?= ApiTokenDetails::backEndBaseUrl(); ?>";
    const siteHostAddress = "<?= ltSiteHostAddress() ?>";
    const getTokenName = "<?= ApiTokenDetails::tokenName(); ?>";

    const LW_TOKEN = localStorage.getItem(`${getTokenName}`);
    const USERID = localStorage.getItem('userId');

function formatTime(timeString, includeSeconds = false) {
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    };
    
    if (includeSeconds) {
        options.second = '2-digit';
    }
    
    return new Date(timeString).toLocaleString('en-US', options);
}    
    

async function ajaxRequest(url, params = {}, method = 'GET'){
    const isFormData = params instanceof FormData;
    
    headers= {
        'Authorization': `Bearer ${LW_TOKEN}`,
    }
    
    if(!isFormData){
        headers['Accept'] = 'application/json';
    }
    
    options = {
        method: method,
        headers: headers
    }
    
    if(method == 'GET'){
        let query = new URLSearchParams(params).toString();
        url += query ? `?${query}` : '';
    }else{
        options.body = isFormData ? params : JSON.stringify(params);
    }
    
    try{
        
        const response = await fetch(`${BASE_URL}` + url, options);
        
        const data = await response.json();
        
        if(data.responseCode == '1102'){
            Swal.fire('Oops!!!', `${data.responseResult}`, 'warning').then(function(){
                localStorage.removeItem('lwToken');
                localStorage.removeItem('userId');
                setTimeout(() => window.location.href = `${siteHostAddress}/`, 2000);
            })
        }else if(data.responseCode == '1103' || data.responseCode == '1105'){
            Swal.fire('Oops!!!', `${data.responseResult}`, 'warning').then(function(){
                localStorage.removeItem('lwToken');
                localStorage.removeItem('userId');
                setTimeout(() => window.location.href = `${siteHostAddress}/`, 2000);
            })
        }
        return data;
    }catch(error){
        throw new Error('Failed to fetch record');
    }
    
    
} 

 let loaderCount = 0;
function fx_lifetech_button_loader_open() {
  loaderCount++;

  let loaderEl = document.querySelector('.lifetech_button_loader');
  let overlayEl = document.querySelector('.lifetech_page_overlay');

  if (!overlayEl) {
    overlayEl = document.createElement('div');
    overlayEl.className = 'lifetech_page_overlay';
    document.body.appendChild(overlayEl);
  }

  if (!loaderEl) {
    loaderEl = document.createElement('div');
    loaderEl.className = 'lifetech_button_loader';
    document.body.appendChild(loaderEl);
  }

  overlayEl.style.display = 'block';
  loaderEl.style.display = 'block';
}

function fx_lifetech_button_loader_close() {
  loaderCount--;

  if (loaderCount <= 0) {
    loaderCount = 0;

    const loaderEl = document.querySelector('.lifetech_button_loader');
    const overlayEl = document.querySelector('.lifetech_page_overlay');

    if (loaderEl) loaderEl.style.display = 'none';
    if (overlayEl) overlayEl.style.display = 'none';
  }
}


    async function fetchRequest(url, method){
      
        try{
            
                const response = await fetch(`${BASE_URL}/${url}`, {
                        method: method,
                        headers: {
                            'Accept': 'application/json'
    
                        }
                    });
                    if(!response.ok) throw new Error('Server Error');
                const res = await response.json();
                return res;
             
        }catch(error){
             
             throw new Error(error);
        }
         
     }



</script> 