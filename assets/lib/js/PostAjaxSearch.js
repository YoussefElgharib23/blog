const axios = require('axios').default;

function searchPost(htmlResults)
{
    let url = 'https://blog.wip/ajax/posts/get/all',
        title = $('#search-in-post-js').val();

    axios.post(url, {
        'PostTitle': title,
    })
    .then(function (response) {
        $('.posts').css('display', 'none');
        htmlResults.html('');
        $('.posts').html('');
        if ( response.data.length > 0 ) {
            response.data.forEach(function (post) {
                htmlResults.css('display', 'flex');
                htmlResults.append(`
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div style="position: relative" class="article-image">
                            <a href="https://blog.wip/${post.slug}-${post.id}">
                                <img alt=""
                                     class="rounded img-fluid mw-100"
                                     src="${post.imageLink}">
                            </a>
                        </div>
                        <small>
                            <span style="color: #fe4f4f">${post.category.name}</span>
                            - ${post.formattedCreatedAt}
                        </small>
                        <h2 class="_font-change-28 h5">
                            <a class="link-c text-decoration-none"
                               href="https://blog.wip/${post.slug}-${post.id}">
                               <span class="post-link-js">${post.title}</span>
                            </a>
                        </h2>
                    </div>
                `);
            });

            let titles = document.querySelectorAll('.post-link-js');
            titles.forEach(function (title) {
                let length = 25;
                let ending = '...';
                if (title.textContent.length > 25) {
                    title.textContent = title.textContent.substring(0, length - ending.length) + ending;
                }
            });
        }
        else {
            $('.posts').css('display', 'block');
            $('.posts').html(`
                <div class="alert alert-danger text-center">No posts found !</div>
            `);
        }
    })
    .catch(function (error) {
        console.log(error.message);
    });
}


$('#search-in-post-js').keyup(function () {
    let htmlResults = $('#posts-results-js');
    searchPost(htmlResults);
});