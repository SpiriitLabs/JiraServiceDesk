/**
 * Theme: Hyper - Responsive Bootstrap 5 Admin Dashboard
 * Author: Coderthemes
 * Module/App: Products demo page
 */


document.addEventListener('DOMContentLoaded', () => {
    const tableElement = document.getElementById('products-datatable');
    if (tableElement) {
        new DataTable(tableElement, {
            columnDefs: [
                {
                    orderable: false,
                    render: DataTable.render.select(),
                    targets: 0
                },
                {
                    orderable: false,
                    searchable: false,
                    targets: -1 // -1 targets the last column
                }
            ],
            language: {
                paginate: {
                    first: '<i class="ri-arrow-left-double-line"></i>',
                    previous: '<i class="ri-arrow-left-s-line"></i>',
                    next: '<i class="ri-arrow-right-s-line"></i>',
                    last: '<i class="ri-arrow-right-double-line"></i>'
                }
            },
            select: {
                style: 'multi',
                selector: 'td:first-child'
            },
            order: [[1, 'asc']]
        });
    }
});