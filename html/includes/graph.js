// Load visualization API
// google.load('visualization', '1.0', {'packages':['corechart']});
google.charts.load("current", {packages:["calendar"]});

function chartHeight(years){
	// Magic formula I calculated
	return 145*years + 25;
}

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
			for(var i=0; i<data.data.length; i++){
				var splitDate = data.data[i][0].split('/');
				rows[i] = [new Date(splitDate[0],splitDate[1]-1,splitDate[2]),data.data[i][1]];
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
				colorAxis: {colors: ['#ee0','#e00']}
			};
			chart.draw(dataTable,options);
		},
		error: function(data){
			console.log(data);
		}
	} );
}

function drawPasswordSetChart(){
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
			for(var i=0; i<data.data.length; i++){
				var splitDate = data.data[i][0].split('/');
				rows[i] = [new Date(splitDate[0],splitDate[1]-1,splitDate[2]),data.data[i][1]];
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
				colorAxis: {colors: ['#ee0','#e00']}
			};
			chart.draw(dataTable,options);
		},
		error: function(data){
			console.log(data);
		}
	} );
}