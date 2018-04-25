// Load visualization API
// google.load('visualization', '1.0', {'packages':['corechart']});
google.charts.load("current", {packages:["calendar"]});

function drawCreatedUserChart(){
	var element = 'created-chart';
	$.ajax( 'graph.php', {
		data: {
			'graph': 'usercal'
		},
		dataType: 'json',
		success: function (data){
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn({type: 'date', id: 'Date'});
			dataTable.addColumn({type: 'number', id: 'Users Created'});
			
			var rows = [];
			for(var i=0; i<data.length; i++){
				var splitDate = data[i][0].split('/');
				rows[i] = [new Date(splitDate[0],splitDate[1]-1,splitDate[2]),data[i][1]];
			}
			dataTable.addRows(rows);
			var chart = new google.visualization.Calendar(document.getElementById(element));
			var options = {
				title: 'Users created',
				height: 750,
				noDataPattern: {
					backgroundColor: 'white',
					color: '#eee'
				},
				colorAxis: {colors: ['#ee0','#e00']}
			};
			chart.draw(dataTable,options);
		}
	} );
}

function drawPasswordSetChart(){
	console.log('passwordchart');
	var element = 'password-chart';
	$.ajax( 'graph.php', {
		data: {
			'graph': 'passcal'
		},
		dataType: 'json',
		success: function (data){
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn({type: 'date', id: 'Date'});
			dataTable.addColumn({type: 'number', id: 'Passwords set'});
			
			var rows = [];
			for(var i=0; i<data.length; i++){
				var splitDate = data[i][0].split('/');
				rows[i] = [new Date(splitDate[0],splitDate[1]-1,splitDate[2]),data[i][1]];
			}
			dataTable.addRows(rows);
			var chart = new google.visualization.Calendar(document.getElementById(element));
			var options = {
				title: 'Password Last Set',
				height: 1620,
				noDataPattern: {
					backgroundColor: 'white',
					color: '#eee'
				},
				colorAxis: {colors: ['#ee0','#e00']}
			};
			chart.draw(dataTable,options);
		},
		error: function(data){
			console.log(data);
		}
	} );
}