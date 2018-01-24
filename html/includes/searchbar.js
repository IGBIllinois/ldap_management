var searchbar_seqnum = 0;
var showingResults;
var searchbar_selected = -1;
var searchbar_max = 0;
function searchbar_search(){
	var searchtext = $('#searchbar').val();
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
			if(searchbar_seqnum == seqnum){
				var results = JSON.parse(data);
				var $searchbar = $('#searchbar');
				var $searchresults = $('#searchbar-results');
				$searchresults.empty();
				showingResults = false;
				var index = 0;
				if(results.hasOwnProperty('users') && results.users.count > 0){
					showingResults = true;
					$searchresults.append('<h6 class="dropdown-header">Users</h6>');
					for(var i=0; i<results.users.results.length; i++){
						$searchresults.append('<a class="searchbar-result dropdown-item" data-searchbarindex="'+index+'" href="user.php?uid='+results.users.results[i].username+'">'+results.users.results[i].name+" ("+results.users.results[i].username+")</a>");
						index++;
					}
					if(results.users.count > results.users.results.length){
						$searchresults.append('<a class="searchbar-result dropdown-item" data-searchbarindex="'+index+'" href="list_users.php?search='+searchtext+'">See all '+results.users.count+' users...</a>');
						index++;
					}
				}
				if(results.hasOwnProperty('groups') && results.groups.count > 0){
					showingResults = true;
					$searchresults.append('<h6 class="dropdown-header">Groups</h6>');
					for(var i=0; i<results.groups.results.length; i++){
						$searchresults.append('<a class="searchbar-result dropdown-item" data-searchbarindex="'+index+'" href="group.php?gid='+results.groups.results[i].name+'">'+results.groups.results[i].name+"</a>");
						index++;
					}
					if(results.groups.count > results.groups.results.length){
						$searchresults.append('<a class="searchbar-result dropdown-item" data-searchbarindex="'+index+'" href="list_groups.php?search='+searchtext+'">See all '+results.groups.count+' groups...</a>');
					}
				}
				searchbar_max = index;
				
				if(showingResults){
					$searchresults.show();
					$searchresults.css('top',$searchbar[0].offsetHeight+$searchbar[0].offsetTop);
					$searchresults.css('min-width',$searchbar[0].offsetWidth);
				} else {
					$searchresults.hide();
				}
			}
		};
}

function searchbar_show(e){
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

function searchbar_handle_keys(e){
	switch(e.keyCode){
		case 38: // Up arrow
			searchbar_selected--;
			if(searchbar_selected<0){
				searchbar_selected = searchbar_max;
			}
			$("#searchbar-results a").removeClass("selected");
			$("#searchbar-results a[data-searchbarindex='"+searchbar_selected+"']").addClass("selected");
			break;
		case 40: // Down arrow
			searchbar_selected++;
			if(searchbar_selected>searchbar_max){
				searchbar_selected = 0;
			}
			$("#searchbar-results a").removeClass("selected");
			$("#searchbar-results a[data-searchbarindex='"+searchbar_selected+"']").addClass("selected");
			break;
		case 13: // Return
			$("#searchbar-results a[data-searchbarindex='"+searchbar_selected+"']")[0].click();
			break;
		default: 
			searchbar_selected = -1;
			return true;
	}
	
	e.preventDefault();
}

function searchbar_hover(e){
	searchbar_selected = -1;
	$("#searchbar-results a").removeClass("selected");
}

function searchbar_setcookies(e){
	Cookies.set('lastUserSearchSort','username');
	Cookies.set('lastUserSearchAsc','true');
	Cookies.set('lastUserSearchFilter','none');
	Cookies.set('lastUserSearch',$("#searchbar").val());
}

$(document).ready(function(){
	$('#searchbar').on('input',searchbar_search);
	$('#searchbar').on('keydown', searchbar_handle_keys);
	$('#searchbar').on('focus', searchbar_show);
	$('#searchbar-results').on('mouseenter','a', searchbar_hover);
	$('#searchbar-results').on('click','a',searchbar_setcookies);
	$('body').on('click',searchbar_hide);
});