jQuery(document).ready(function($){
    function fetchCustomers(search='') {
        $.post(cdp_ajax.ajax_url, { action: 'cdp_fetch_customers', search: search }, function(data){
            const results = JSON.parse(data);
            let html = '<ul>';
            results.forEach(c => html += `<li>${c.name} - ${c.email} - ${c.status}</li>`);
            html += '</ul>';
            $('#cdp-results').html(html);
        });
    }

    fetchCustomers();

    $('#cdp-search').on('keyup', function(){
        fetchCustomers($(this).val());
    });
});
