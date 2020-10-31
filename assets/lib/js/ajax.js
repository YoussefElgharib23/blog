const axios = require('axios').default;

$('input#search-in-js').keyup(function () {
    let name = $(this).val(),
        divRes = $('.t-d-n-js');
    axios.post('https://127.0.0.1:8000/category/find',
        {
            Name: name
        })
        .then(function (results) {
            divRes.html(`
                <table class="mt-3 table table-hover">
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
            $('#result-category-search-js').html(``);
            if (results.data.length > 0) {
                results.data.forEach(function (category) {
                    $('#result-category-search-js').append(`
                    <tr>
                        <td>${category['name']}</td>
                        <td>${category['formattedCreatedAt']}</td>
                        <td>${category['formattedUpdatedAt']}</td>
                        <td>
                            <a href="https://127.0.0.1:8000/category/${category['id']}/edit" class="btn btn-outline-info btn-sm">
                                <i class="fa fa-pen"></i>
                                Edit
                            </a>
                            <a class="btn btn-outline-danger btn-sm" id="delete-link-js">
                                <i class="fa fa-trash"></i>
                                <span id="cat-id" style="display: none;">${category['id']}</span>
                                Delete
                            </a>
                            <form id="form-delete-category-js-${category['id']}" action="https://127.0.0.1:8000/category/${category['id']}/delete"
                                method="POST" style="display: none;" onsubmit="return confirm('Are you sure you wanna delete the category !')">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="_token" value="{{ csrf_token('delete_category' ~ category.id) }}">
                            </form>
                        </td>
                    </tr>
                `);
                });
            }
            else if (results.data.length === 0) {
                divRes.html(`
                    <div class="mt-4 alert alert-danger text-center">Nothing found</div>
                `);
            }
        });
});

