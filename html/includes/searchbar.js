var searchbar_seqnum = 0;
var showingResults;
function searchbar_search(){
	var searchtext = $('#searchbar').val();
	console.log("Searching: '"+searchtext+"'");
	$.ajax('searchbar_data.php',{
		method: 'GET',
		data: {'searchtext':searchtext},
		success: searchbarResultsProcessor(searchtext)
	});
}

function searchbarResultsProcessor(searchtext){
	searchbar_seqnum++;
	var seqnum = searchbar_seqnum;
	return function(data){
		console.log(searchbar_seqnum, seqnum);
			if(searchbar_seqnum == seqnum){
				var results = JSON.parse(data);
				console.log(results);
				var $searchbar = $('#searchbar');
				var $searchresults = $('#searchbar-results');
				$searchresults.empty();
				showingResults = false;
				if(results.hasOwnProperty('users') && results.users.count > 0){
					showingResults = true;
					$searchresults.append('<div class="searchbar-heading">Users</div>');
					for(var i=0; i<results.users.results.length; i++){
						$searchresults.append('<a class="searchbar-result" href="user.php?uid='+results.users.results[i].username+'">'+results.users.results[i].name+"</a>");
					}
					if(results.users.count > results.users.results.length){
						$searchresults.append('<a class="searchbar-result" href="list_users.php?search='+searchtext+'">See all '+results.users.count+' users...</a>');
					}
				}
				if(results.hasOwnProperty('groups') && results.groups.count > 0){
					showingResults = true;
					$searchresults.append('<div class="searchbar-heading">Groups</div>');
					for(var i=0; i<results.groups.results.length; i++){
						$searchresults.append('<a class="searchbar-result" href="group.php?gid='+results.groups.results[i].name+'">'+results.groups.results[i].name+"</a>");
					}
					if(results.groups.count > results.groups.results.length){
						$searchresults.append('<a class="searchbar-result" href="list_groups.php?search='+searchtext+'">See all '+results.groups.count+' groups...</a>');
					}
				}
				if(showingResults){
					$searchresults.show();
					$searchresults.css('top',$searchbar[0].offsetHeight+$searchbar[0].offsetTop);
					$searchresults.css('width',$searchbar[0].offsetWidth);
				} else {
					$searchresults.hide();
				}
			}
		};
}

function searchbar_show(e){
	console.log(e);
	if(showingResults){
		$('#searchbar-results').show();
	}
}
function searchbar_hide(e){
	var $target = $(e.target);
	if($target.closest('#searchbar-container').length == 0){
		$('#searchbar-results').hide();
	}
}

$(document).ready(function(){
	$('#searchbar').on('input',searchbar_search);
	$('#searchbar').on('focus', searchbar_show);
	$('body').on('click',searchbar_hide);
});