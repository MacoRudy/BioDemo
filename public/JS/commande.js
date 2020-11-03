$(document).ready(function () {

    $('#client').on('change', function () {
        // les 2 méthodes sont valables
        // let client = $('#client option:selected')
        let client = $(this).find('option:selected');
        let idclient = client.val();
        console.log(idclient);

        $.ajax({
            url: 'commande/trier/global',
            type: 'POST',
            dataType: 'JSON',
            data: 'idclient='+5,
            async: true,
            error: function (xhr, textStatus, errorThrown) {
                alert('Ajax request failed.');
            }
        })

            .done(function (response) {

                console.log(response)
            })
    })

});

