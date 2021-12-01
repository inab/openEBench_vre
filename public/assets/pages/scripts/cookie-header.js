var baseURL = $('#base-url').val();
if(Cookies.get('currentPage')) {
		//cookie      = Cookies.get('currentPage');
		cookie_clean= Cookies.get('currentPage').replace(/\/\/+/g, '/');
		cookie_neat = cookie_clean.replace(/\/$/, "");
		baseURL_neat= baseURL.replace(/\/$/, "");
		if (baseURL_neat == cookie_neat){
			location.href = baseURL + 'home/';	
		}else{
			location.href = Cookies.get('currentPage');	
		}
} else {
		location.href = baseURL + 'home/';	
}

