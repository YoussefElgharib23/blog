const axios = require('axios').default;

$('input#search-in-post-js').keyup(async function () {
    let valInput = $(this).val(),
        url = 'https://127.0.0.1:8000/post/find';

    axios.post(url,
        {
            title: valInput
        })
        .then(function (results) { console.log(results.data); })
        .catch(function (error) { console.log(error.message); });
});