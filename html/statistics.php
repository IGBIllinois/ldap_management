<?php
	$title = "Statistics";
	require 'includes/header.inc.php';
?>
	<h3>Statistics</h3>
	<table class='table table-condensed table-striped table-bordered'>
		<tr>
			<td># of Users</td>
			<td><?php echo statistics::users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Expiring Users</td>
			<td><?php echo statistics::expiring_users($ldap)-statistics::expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Expired Users</td>
			<td><?php echo statistics::expired_users($ldap); ?></td>
		</tr>
		<tr>
			<td># of Groups</td>
			<td><?php echo statistics::groups($ldap); ?></td>
		</tr>
		<tr>
			<td># of Empty Groups</td>
			<td><?php echo statistics::empty_groups($ldap); ?></td>
		</tr>
	</table>
	
<!--
	<div id="calendar" style="width:100%;height:500px"></div>
	
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
	<script type="text/javascript">
		google.charts.load("current",{packages:["calendar"]});
		google.charts.setOnLoadCallback(drawChart);
		
		function drawChart(){
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn({type:'date',id:'Date'});
			dataTable.addColumn({type:'number',id:'Users'});
			$.ajax("graph.php",{
				async:false,
				data:{'graph':'usercal'},
				success:function(data){
					var calendar = JSON.parse(data);
					console.log(calendar);
					var rows = [];
					for (var key in calendar){
						rows[rows.length] = [new Date(key),calendar[key]];
					}
					console.log(rows);
					dataTable.addRows(rows);
					var chart = new google.visualization.Calendar(document.getElementById('calendar'));
					var options = {
						title: "User Creation"
					};
					chart.draw(dataTable,options);
				}
			});
		}
	</script>
-->
	
<?php
	require 'includes/footer.inc.php';