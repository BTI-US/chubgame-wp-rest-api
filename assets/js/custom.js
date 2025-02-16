jQuery(document).ready(function($) {
    function testEndpoint(endpoint, jsonBody, resultDiv) {
        $.ajax({
            url: awpr_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'awpr_test_endpoint',
                endpoint: endpoint,
                json_body: jsonBody
            },
            success: function(response) {
                $(resultDiv).html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
            },
            error: function(xhr, status, error) {
                $(resultDiv).html('<pre>' + xhr.responseText + '</pre>');
            }
        });
    }

    $('#awpr-test-button-validate').click(function() {
        var jsonBody = $('#awpr-json-body-validate').val();
        testEndpoint('validate', jsonBody, '#awpr-test-result-validate');
    });

    $('#awpr-test-button-check-balance').click(function() {
        var jsonBody = $('#awpr-json-body-check-balance').val();
        testEndpoint('check-balance', jsonBody, '#awpr-test-result-check-balance');
    });

    $('#awpr-test-button-send').click(function() {
        var jsonBody = $('#awpr-json-body-send').val();
        testEndpoint('send', jsonBody, '#awpr-test-result-send');
    });
});
