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
				console.log(splitDate);
				rows[i] = [new Date(splitDate[0],splitDate[1]-1,splitDate[2]),data[i][1]];
			}
			dataTable.addRows(rows);
			var chart = new google.visualization.Calendar(document.getElementById(element));
			var options = {
				title: 'Users created',
				height: 750
			};
			chart.draw(dataTable,options);
		}
	} );
}