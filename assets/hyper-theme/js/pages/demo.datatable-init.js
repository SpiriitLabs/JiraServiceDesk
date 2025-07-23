/**
 * Theme: Hyper - Responsive Bootstrap 5 Admin Dashboard
 * Author: Coderthemes
 * Module/App: Data tables 
 */


//
// Basic Datatables
//
new DataTable('#basic-datatable', {
    language: {
        paginate: {
            first: '<i class="ri-arrow-left-double-line align-middle"></i>',
            previous: '<i class="ri-arrow-left-s-line align-middle"></i>',
            next: '<i class="ri-arrow-right-s-line align-middle"></i>',
            last: '<i class="ri-arrow-right-double-line align-middle"></i>'
        }
    }
});

//
// Buttons Options
//
document.addEventListener('DOMContentLoaded', () => {
    const exportDataTable = document.querySelector('#datatable-buttons');
    if (exportDataTable) {
        new DataTable(exportDataTable, {
            dom: "<'d-md-flex justify-content-between align-items-center my-2'Bf>" +
                "rt" +
                "<'d-md-flex justify-content-between align-items-center mt-2'ip>",
            responsive: true,
            buttons: [
                {extend: 'copy', className: 'btn btn-sm btn-secondary'},
                {extend: 'csv', className: 'btn btn-sm btn-secondary active'},
                {extend: 'excel', className: 'btn btn-sm btn-secondary'},
                {extend: 'print', className: 'btn btn-sm btn-secondary active'},
                {extend: 'pdf', className: 'btn btn-sm btn-secondary'}
            ],
            language: {
                paginate: {
                    first: '<i class="ri-arrow-left-double-line align-middle"></i>',
                    previous: '<i class="ri-arrow-left-s-line align-middle"></i>',
                    next: '<i class="ri-arrow-right-s-line align-middle"></i>',
                    last: '<i class="ri-arrow-right-double-line align-middle"></i>'
                }
            }
        });
    }
});

//
// Select Options
//
document.addEventListener('DOMContentLoaded', () => {
    const exportDataTable = document.querySelector('#selection-datatable');
    if (exportDataTable) {
        new DataTable(exportDataTable, {
            pageLength: 7,
            lengthMenu: [7, 10, 25, 50, -1],
            select: "multi", //single, or 'os', items: 'cell' also available
            language: {
                paginate: {
                    first: '<i class="ri-arrow-left-double-line align-middle"></i>',
                    previous: '<i class="ri-arrow-left-s-line align-middle"></i>',
                    next: '<i class="ri-arrow-right-s-line align-middle"></i>',
                    last: '<i class="ri-arrow-right-double-line align-middle"></i>'
                },
                lengthMenu: '_MENU_ Items per page',
                info: 'Showing <span class="fw-semibold">_START_</span> to <span class="fw-semibold">_END_</span> of <span class="fw-semibold">_TOTAL_</span> Items'
            }
        });
    }
});

//
// Scroll Options
//
document.addEventListener('DOMContentLoaded', function () {
    const tableElement = document.getElementById('scroll-datatable');
    if (tableElement) {
        new DataTable(tableElement, {
            paging: false,              // Disable pagination
            scrollCollapse: true,       // Allow table to collapse
            scrollY: '320px',           // Vertical scrolling
        });

        new DataTable('#horizontal-scroll', {
            scrollX: true,
            language: {
                paginate: {
                    first: '<i class="ri-arrow-left-double-line align-middle"></i>',
                    previous: '<i class="ri-arrow-left-s-line align-middle"></i>',
                    next: '<i class="ri-arrow-right-s-line align-middle"></i>',
                    last: '<i class="ri-arrow-right-double-line align-middle"></i>'
                },
                lengthMenu: '_MENU_ Items per page',
                info: 'Showing <span class="fw-semibold">_START_</span> to <span class="fw-semibold">_END_</span> of <span class="fw-semibold">_TOTAL_</span> Items'
            }
        });
    }
})

//
// Complex Header 
//
new DataTable('#complex-header-datatable', {
    language: {
        paginate: {
            first: '<i class="ri-arrow-left-double-line align-middle"></i>',
            previous: '<i class="ri-arrow-left-s-line align-middle"></i>',
            next: '<i class="ri-arrow-right-s-line align-middle"></i>',
            last: '<i class="ri-arrow-right-double-line align-middle"></i>'
        }
    }
});


//
// Fixed Header 
//
document.addEventListener('DOMContentLoaded', () => {
    const tableElement = document.getElementById('fixed-header-datatable');
    if (tableElement) {
        new DataTable(tableElement, {
            fixedHeader: {
                header: true,
                headerOffset: 70
            },
            pageLength: 15,
            language: {
                paginate: {
                    first: '<i class="ri-arrow-left-double-line align-middle"></i>',
                    previous: '<i class="ri-arrow-left-s-line align-middle"></i>',
                    next: '<i class="ri-arrow-right-s-line align-middle"></i>',
                    last: '<i class="ri-arrow-right-double-line align-middle"></i>'
                }
            }
        });
    }
})