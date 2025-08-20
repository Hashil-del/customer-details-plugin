jQuery(document).ready(function($){
    function fetchCustomers(search='') {
        $.post(cdp_front_ajax.ajax_url, { action: 'cdp_fetch_front_customers', search: search }, function(data){
            const results = JSON.parse(data);
            let html = '<ul>';
            results.forEach(c => html += `<li>${c.name} - ${c.email}</li>`);
            html += '</ul>';
            $('#cdp-results').html(html);
        });
    }

    fetchCustomers();

    $('#cdp-search').on('keyup', function(){
        fetchCustomers($(this).val());
    });
});
