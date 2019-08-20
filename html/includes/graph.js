// Load visualization API
// google.load('visualization', '1.0', {'packages':['corechart']});
google.charts.load("current", {packages: ["calendar", "line"]});

function chartHeight(years) {
    // Magic formula I calculated
    return 145 * years + 25;
}

function drawCreatedUserChart() {
    var element = 'created-chart';
    $.ajax('graph.php', {
        data: {
            'graph': 'usercal'
        },
        dataType: 'json',
        success: function (data) {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn({type: 'date', id: 'Date'});
            dataTable.addColumn({type: 'number', id: 'Users Created'});

            var rows = [];
            for (var i = 0; i < data.data.length; i++) {
                var splitDate = data.data[i][0].split('/');
                rows[i] = [new Date(splitDate[0], splitDate[1] - 1, splitDate[2]), data.data[i][1]];
            }
            dataTable.addRows(rows);
            var chart = new google.visualization.Calendar(document.getElementById(element));
            var options = {
                title: 'Users created',
                height: chartHeight(data.years),
                noDataPattern: {
                    backgroundColor: 'white',
                    color: '#eee'
                },
                colorAxis: {colors: ['#ee0', '#e00']}
            };
            chart.draw(dataTable, options);
        },
        error: function (data) {
            console.log(data);
        }
    });
}

function drawPasswordSetChart() {
    var element = 'password-chart';
    $.ajax('graph.php', {
        data: {
            'graph': 'passcal'
        },
        dataType: 'json',
        success: function (data) {
            var dataTable = new google.visualization.DataTable();
            dataTable.addColumn({type: 'date', id: 'Date'});
            dataTable.addColumn({type: 'number', id: 'Passwords set'});

            var rows = [];
            for (var i = 0; i < data.data.length; i++) {
                var splitDate = data.data[i][0].split('/');
                rows[i] = [new Date(splitDate[0], splitDate[1] - 1, splitDate[2]), data.data[i][1]];
            }
            dataTable.addRows(rows);
            var chart = new google.visualization.Calendar(document.getElementById(element));
            var options = {
                title: 'Password Last Set',
                height: chartHeight(data.years),
                noDataPattern: {
                    backgroundColor: 'white',
                    color: '#eee'
                },
                colorAxis: {colors: ['#ee0', '#e00']}
            };
            chart.draw(dataTable, options);
        },
        error: function (data) {
            console.log(data);
        }
    });
}

function drawUsersOverTimeChart() {
    const element = 'users-chart';
    $.ajax('graph.php', {
        data: {
            'graph': 'userline'
        },
        dataType: 'json',
        success: function (data) {
            const dataTable = new google.visualization.DataTable();
            dataTable.addColumn('date', 'Date');
            dataTable.addColumn('number', 'Users');
            dataTable.addRows(data.map(x => [new Date(x[0]), x[1]]));
            const options = {
                title: 'Users',
                height: 400,
                legend: {position: 'none'},
                vAxis: {gridlines: {count: 5, color: '#ddd'},
                minorGridlines: {count: 1, color: '#EEE'}}
            };
            const chart = new google.charts.Line(document.getElementById(element));

            chart.draw(dataTable, google.charts.Line.convertOptions(options));
        },
        error: function (data) {
            console.log(data);
        }
    })
}

function drawMembersOverTimeChart(group) {
    return function () {
        const element = 'members-chart';
        $.ajax('graph.php', {
            data: {
                'graph': 'memberline',
                'group': group
            },
            dataType: 'json',
            success: function (data) {
                console.log(data)
                const dataTable = new google.visualization.DataTable();
                dataTable.addColumn('date', 'Date');
                dataTable.addColumn('number', 'Members');
                dataTable.addRows(data.map(x => [new Date(x[0]), x[1]]));
                const options = {
                    title: 'Members',
                    height: 200,
                    legend: {position: 'none'},
                    vAxis: {gridlines: {count: 5}}
                };
                const chart = new google.charts.Line(document.getElementById(element));

                chart.draw(dataTable, google.charts.Line.convertOptions(options));
            },
            error: function (data) {
                console.log(data);
            }
        });
    }
}