(( $, d ) => {

    const { ajax_url, action, nonce, route } = window.c_debi_plugin

    $(d).ready(() => {

        OneTimeForm($('#one-time'))

    });

    function OneTimeForm(form) {

        form.on('submit', function(e){
            e.preventDefault()
            const messageEl =  $(this).find('.status .message').eq(0)
            messageEl.html('')

            let reqs = [];
            $(this).find('input[type="checkbox"]:checked').each( (i, v) => {
                reqs.push(JSON.parse(v.value))
            } );

            if (reqs.length) {
                //const pollInterval = setPollInterval(reqs, statusEl)
                triggerActions(reqs, messageEl)
                messageEl.html("Awaiting response...")
            }

            return false;

        });
    }

    function triggerActions(reqs, messageEl, pollInterval = null){
        $.ajax({
            url: ajax_url, type: 'POST',
            data: {action, nonce, route, reqs},
            error: (jqXHR, textStatus, errorThrown) => {
                console.log(errorThrown)
            },
            success: (res) => {
                if (pollInterval) {
                    window.clearInterval(pollInterval);
                }
                messageEl.html(`<pre>${JSON.stringify(res)}</pre>`);
            }
        })
    }

    function setPollInterval(reqs, messageEl) {
        return window.setInterval(function(){
            const status_reqs = reqs.map(req => ({ method: 'ActionStatus->get_status', payload: req.handler }))
            $.ajax({ url: ajax_url, route,
                data: { action, nonce, route, reqs: status_reqs }
            }).done(function(res){
                messageEl.append(`<pre>${JSON.stringify(JSON.parse(res), null, 2)}</pre>`);
            });
        }, 5000);
    }

})( jQuery, document );