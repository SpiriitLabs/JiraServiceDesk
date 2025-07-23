/**
 * Theme: Hyper - Responsive Bootstrap 5 Admin Dashboard
 * Author: Coderthemes
 * Module/App: Sellers demo page
 */

document.addEventListener('DOMContentLoaded', () => {
    const tableElement = document.getElementById('sellers-datatable');

    if (!tableElement) return;

    const options = {
        chart: {
            type: 'line',
            width: 80,
            height: 35,
            sparkline: {
                enabled: true
            }
        },
        series: [],
        stroke: {
            width: 2,
            curve: 'smooth'
        },
        markers: {
            size: 0
        },
        colors: ['#727cf5'],
        tooltip: {
            fixed: { enabled: false },
            x: { show: false },
            y: {
                title: {
                    formatter: () => ''
                }
            },
            marker: { show: false }
        }
    };

    let charts = [];

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
                targets: -1
            }
        ],
        language: {
            paginate: {
                first: '<i class="ri-arrow-left-double-line"></i>',
                previous: '<i class="ri-arrow-left-s-line"></i>',
                next: '<i class="ri-arrow-right-s-line"></i>',
                last: '<i class="ri-arrow-right-double-line"></i>'
            },
            info: 'Showing sellers _START_ to _END_ of _TOTAL_',
            lengthMenu:
                "Display <select class='form-select form-select-sm ms-1 me-1'>" +
                "<option value='10'>10</option>" +
                "<option value='20'>20</option>" +
                "<option value='-1'>All</option>" +
                "</select> sellers"
        },
        pageLength: 10,
        select: {
            style: 'multi',
            selector: 'td:first-child'
        },
        order: [[1, 'asc']],
        drawCallback: function () {
            // UI enhancements
            document
                .querySelectorAll('.dataTables_paginate > .pagination')
                .forEach((el) => el.classList.add('pagination-rounded'));

            document
                .querySelectorAll('#products-datatable_length label')
                .forEach((el) => el.classList.add('form-label'));

            // Destroy old charts
            charts.forEach((chart) => {
                try {
                    chart.destroy();
                } catch (e) {
                    console.error(e);
                }
            });
            charts = [];

            // Render ApexCharts sparklines
            document.querySelectorAll('.spark-chart').forEach((el) => {
                const dataset = el.dataset.dataset
                    ? JSON.parse(el.dataset.dataset)
                    : [];
                options.series = [{ data: dataset }];
                const chart = new ApexCharts(el, options);
                chart.render();
                charts.push(chart);
            });
        }
    });
});



