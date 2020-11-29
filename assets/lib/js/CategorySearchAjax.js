const axios = require('axios').default;

const searchBox = $('#search-in-js');

searchBox.keyup(async function () {
    let value = searchBox.val().trim().toLowerCase(),
        url = 'https://blog.wip/category/get',
        parent = $('tbody#result-category-search-js').parent(),
        parentDiv = $('div.t-d-n-js');
    axios.post(url, {
        title: value
    })
    .then(function (response) {
        if (response.data.length > 0) {
            if ($('table.result-table-js')) {
                parentDiv.html('');
                $('div.t-d-n-js').append(`
                    <table class="mt-3 table table-hover result-table-js">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Created At</th>
                        <th>Latest update</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="result-category-search-js">
                    </tbody>
                </table>
                `);
                response.data.forEach(function (category) {
                    $('tbody#result-category-search-js').append(`
                        <tr>
                            <td>${category.name}</td>
                            <td>${category.formattedCreatedAt}</td>
                            <td>${category.formattedUpdatedAt}</td>
                            <td class="d-flex-col">
                                <a href="https://${window.location.hostname}/category/${category.id}/edit"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fa fa-pen"></i>
                                    Edit
                                </a>
                                <a class="btn btn-outline-danger btn-sm d-flex-md align-items-center" id="delete-link-js" href="https://${window.location.hostname}/category/${category.id}/delete">
                                    <i class="fa fa-trash"></i>
                                    Delete
                                </a>
                            </td>
                        </tr>
                    `);
                });
            }
        } else {
            parentDiv.html('');
            parent.remove();
            parentDiv.append($('<div class="alert alert-danger text-center" id="error-result-js">No category found !</div>'));
        }
    })
    .catch(error => {
        console.log(error.message);
    });
});