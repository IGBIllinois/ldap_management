// Load visualization API
// google.load('visualization', '1.0', {'packages':['corechart']});
google.charts.load("current", {packages: ["calendar", "line"]});

let darkMode = window.matchMedia('(prefers-color-scheme: dark)');

function chartHeight(years) {
    // Magic formula I calculated
    return 145 * years + 25;
}

function drawCreatedUserChart() {
    var element = 'created-chart';

    let options = {
        title: 'Users created',
        noDataPattern: {
            backgroundColor: 'transparent'
        },
        colorAxis: {colors: ['#ee0', '#e00']}
    };

    if(darkMode.matches){
        options.noDataPattern.color = '#495057';
        options.colorAxis = {
            colors: ['#aa0', '#a00']
        };
        options.calendar = {
            monthLabel: {
                color: '#E9ECEF'
            },
            dayOfWeekLabel: {
                color: '#E9ECEF'
            },
            cellColor: {
                stroke: '#495057'
            },
            unusedMonthOutlineColor: {
                stroke: '#6C757D'
            },
            monthOutlineColor: {
                stroke: '#ADB5BD'
            }
        };
    }

    $.ajax('graph.php', {
        data: {
            'graph': 'usercal'
        },
        dataType: 'json',
        success: function (data) {
            let dataTable = new google.visualization.DataTable();
            dataTable.addColumn({type: 'date', id: 'Date'});
            dataTable.addColumn({type: 'number', id: 'Users Created'});

            let rows = [];
            for (let i = 0; i < data.data.length; i++) {
                var splitDate = data.data[i][0].split('/');
                rows[i] = [new Date(splitDate[0], splitDate[1] - 1, splitDate[2]), data.data[i][1]];
            }
            dataTable.addRows(rows);
            const chart = new google.visualization.Calendar(document.getElementById(element));

            options.height = chartHeight(data.years);

            chart.draw(dataTable, options);
        },
        error: function (data) {
            console.log(data);
        }
    });
}

function drawPasswordSetChart() {
    var element = 'password-chart';

    let options = {
        title: 'Password last set',
        noDataPattern: {
            backgroundColor: 'transparent'
        },
        colorAxis: {colors: ['#ee0', '#e00']}
    };

    if(darkMode.matches){
        options.noDataPattern.color = '#495057';
        options.colorAxis = {
            colors: ['#aa0', '#a00']
        };
        options.calendar = {
            monthLabel: {
                color: '#E9ECEF'
            },
            dayOfWeekLabel: {
                color: '#E9ECEF'
            },
            cellColor: {
                stroke: '#495057'
            },
            unusedMonthOutlineColor: {
                stroke: '#6C757D'
            },
            monthOutlineColor: {
                stroke: '#ADB5BD'
            }
        };
    }

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
            options.height = chartHeight(data.years);
            chart.draw(dataTable, options);
        },
        error: function (data) {
            console.log(data);
        }
    });
}

function drawUsersOverTimeChart() {
    const element = 'users-chart';

    let options = {
        title: 'Users',
        height: 400,
        legend: {position: 'none'},
        vAxis: {
            gridlines: {count: 5, color: '#ddd'},
            minorGridlines: {count: 1, color: '#EEE'}
        },
        backgroundColor: 'transparent'
    };

    if(darkMode.matches){
        options.hAxis = {
            textStyle: {
                color: "#E9ECEF"
            }
        };
        options.vAxis = {
            textStyle: {
                color: "#E9ECEF"
            }
        };
        options.titleTextStyle = {
            color: "#E9ECEF"
        };
    }

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

        let options = {
            title: 'Members',
            height: 200,
            legend: {position: 'none'},
            vAxis: {gridlines: {count: 5}},
            backgroundColor: 'transparent'
        };

        if(darkMode.matches){
            options.hAxis = {
                textStyle: {
                    color: "#E9ECEF"
                }
            };
            options.vAxis = {
                textStyle: {
                    color: "#E9ECEF"
                }
            };
            options.titleTextStyle = {
                color: "#E9ECEF"
            };
        }

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

                const chart = new google.charts.Line(document.getElementById(element));

                chart.draw(dataTable, google.charts.Line.convertOptions(options));
            },
            error: function (data) {
                console.log(data);
            }
        });
    }
}