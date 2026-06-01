$(function(){
    $('#surf_add').submit(function(){
        var url = '/ajax.php?action=surf&type='+$("#type").val();//$(this).attr('action');
        var data = $(this).serialize()+'&ajax=1';
        $.post(url, data, function(result){
            if(result['id'] != ''){
                $('#'+result['id']).addClass('is-invalid');
            }
            swal(result['title'], result['error'], result['status']);
            if(result['status'] == 'success'){
                if(result['redirect'] != ''){
                    setTimeout(function(){
                        window.location.replace(result['redirect']);
                    }, 1000);
                }
            } 
        }, 'json');
        return false;
    });
	
	$('.balance_add').submit(function(){
        var url = '/ajax.php?action=surf&type='+$("#type").val();//$(this).attr('action');
        var data = $(this).serialize()+'&ajax=1';
        $.post(url, data, function(result){
            if(result['id'] != ''){
                $('#'+result['id']).addClass('is-invalid');
            }
            swal(result['title'], result['error'], result['status']);
            if(result['status'] == 'success'){
                if(result['redirect'] != ''){
                    setTimeout(function(){
                        window.location.replace(result['redirect']);
                    }, 1000);
                }
            } 
        }, 'json');
        return false;
    });
	
	$('#surf_edit').submit(function(){
        var url = '/ajax.php?action=surf&type='+$("#type").val();//$(this).attr('action');
        var data = $(this).serialize()+'&ajax=1';
        $.post(url, data, function(result){
            if(result['id'] != ''){
                $('#'+result['id']).addClass('is-invalid');
            }
            swal(result['title'], result['error'], result['status']);
            if(result['status'] == 'success'){
                if(result['redirect'] != ''){
                    setTimeout(function(){
                        window.location.replace(result['redirect']);
                    }, 1000);
                }
            } 
        }, 'json');
        return false;
    });
	
	 $(document).on('click', '.status_surf',function() {
        var button = $(this);
        var surf_id = button.attr('surf_id');
        var status = button.attr('action');
        $.post('/ajax.php?action=surf&type=status', {'surf_id': surf_id,'status':status}, function(result) {
            if(result['status'] == 'success'){
                if(status === '1'){
                    button.attr('action','2');
                    button.html('<i class="fa fa-pause"></i>');
                    button.removeClass('btn-success');
                    button.addClass('btn-warning');
                    button.attr('title', 'Stop');
                }else if(status === '2'){
                    button.attr('action','1');
                    button.html('<i class="fa fa-play"></i>');
                    button.removeClass('btn-warning');
                    button.addClass('btn-success');
                    button.attr('title', 'Start');
                }  
            }
        }, 'json');
        return false;
    });
	
	$(document).on('click', '.delete',function() {
        var surf_id = $(this).attr('surf_id');
        $.post('/ajax.php?action=surf&type=delete', {'surf_id': surf_id}, function(result) {
            swal(result['title'], result['error'], result['status']);
            if(result['status'] == 'success'){
                $('#surf_'+surf_id).remove();
            }
        }, 'json');
        return false;
    });
	
});


