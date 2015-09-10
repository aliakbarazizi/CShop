function handleCardDrop( event, ui )
{
	addItem(ui.draggable.attr('data-id'));
}
function updateprice(price)
{
	$("#total span").html(parseInt($("#total span").html())+price);
}
function generate(text,type) {
    var n = noty({
        text        : text,
        type        : type,
        dismissQueue: true,
        modal       : true,
        layout      : 'top',
        theme       : 'defaultTheme',
        closeWith: ['click', 'button', 'hover']// ['click', 'button', 'hover']
    });
    
    setTimeout(function () {
    	$.noty.close(n.options.id);
    }, 2000);
}
function check(object)
{
	if(!this.checked) { // check select status
        $('[name="delete[]"]').each(function() { //loop through each checkbox
            this.checked = true;  //select all checkboxes with class "checkbox1"              
        });
        this.checked = true;
    }else{
    	this.checked=false;
        $('[name="delete[]"]').each(function() { //loop through each checkbox
            this.checked = false; //deselect all checkboxes with class "checkbox1"                      
        });        
    }
}
function generateAll() {
    generate('alert');
    generate('information');
    generate('error');
    generate('warning');
    generate('notification');
    generate('success');
}
function addItem(id,number)
{
	number = typeof number !== 'undefined' ? number : 1;
	
	var obj = $('.endbuy #'+id);
	if (obj.length)
	{
		obj.stop( true, true ).effect('highlight',{easing : 'easeInCubic'},2000);
		var val = parseInt(obj.find("input[type='text']").val());
		val = val || 1;
		obj.find("input[type='text']").val(val+number);
	}
	else
	{
		var name = $("#cards [data-id='"+id+"'] span.name").html();
		if(number==1)
			$('	<div class="buy" id="'+id+'"><div class="delete"></div>'+name+'<input type="text" placeholder="تعداد" value="1" name="product['+id+']"></div>').hide().appendTo($(".endbuy")).slideDown();
		else
			$('<div class="buy" id="'+id+'"><div class="delete"></div>'+name+'<input type="text" placeholder="تعداد" value="'+number+'" name="product['+id+']"></div>').hide().appendTo($(".endbuy")).slideDown();
		$('.endbuy').perfectScrollbar('update');
		$('.endbuy').animate({ scrollTop: $('.endbuy')[0].scrollHeight }, 800);
	}
	updateprice(parseInt($("#cards [data-id='"+id+"'] span.f").html())*number);
}
$(function(){
	
	var $filteredData;
	
	$(".delete").live('click',function() {
		var $item = $(this).closest('div.buy');
		var val = parseInt($item.find("input[type='text']").val());
		val = val || 1;
		$('.endbuy').perfectScrollbar('destroy');
		updateprice(-1*parseInt($("#cards [data-id='"+$item.attr('id')+"'] span.f").html())*val);
		$item.stop( true, true ).slideUp('slow', function(){ $item.remove();$('#cardslots').perfectScrollbar({suppressScrollX: true}); });
	});
	
	$(".endbuy input").live('keyup',function() {
		$("#total span").html(0);
		$('.endbuy .buy').each(function(){
			var $item = $(this).find('input').val();
			$item = typeof $item !== 'undefined' ? $item : 1;
			var id = $(this).attr('id');
			updateprice(parseInt($("#cards [data-id='"+id+"'] span.f").html()) * parseInt($item));
		}
		);
	});
	
	$(".noty_modal").live('click',function() {
		$.noty.closeAll();
	});
	
	$('#cards .item').live('dblclick',function() {
		addItem($(this).attr('data-id'));
		
	});
	
	$(window).resize(function () {
		
	});
	
	//init plugins
	$('input, textarea').placeholder();
	$('.endbuy').perfectScrollbar({suppressScrollX: true});
	
	$('.endbuy').droppable( {
		accept: '#cards .item',
		hoverClass: 'hovered',
		activeClass : 'active',
		drop: handleCardDrop
		});
		 
	$('#cards .item').draggable( {
		containment: '.content',
		revert:true,
		cursor: 'move',
		helper: "clone",
		appendTo: "#cards",
	});
	
	
	var $applications = $('#cards');
	
	$('#category li').click(function(e) {
		$(this).parent().find('li.active').removeClass('active');
		$(this).addClass('active');
		$applications.find('[data-type!=' + $(this).attr('data-type') + ']').stop( true, true ).hide();
		$applications.find('[data-type=' + $(this).attr('data-type') + ']').stop( true, true ).slideDown(500).children().slideDown(1200);
		
		
		$('#cards .item').draggable( {
			containment: '.content',
			revert:true,
			cursor: 'move',
			helper: "clone",
			appendTo: "#cards",
			}
		);
		e.preventDefault();	
		
	});
	
	$('#category li').first().trigger('click');
	
	$('input[type="text"],textarea').keyup(function(){
	    $this = $(this);
	    if($this.val().length == 1)
	    {
	        var x =  new RegExp("[\x00-\x80]+"); // is ascii

	        //alert(x.test($this.val()));

	        var isAscii = x.test($this.val());

	        if(isAscii)
	        {
	        	$this.css("direction", "ltr");
	        	$this.css("text-align", "left");
	        }
	        else
	        {
	            $this.css("direction", "rtl");
	            $this.css("text-align", "right");
	        }
	    }else if($this.val().length == 0 && $this.attr('placeholder').length != 0 )
	    {
	    	var val = $this.attr('placeholder').charAt(0);
	    	var x =  new RegExp("[\x00-\x80]+"); // is ascii

	        //alert(x.test($this.val()));

	        var isAscii = x.test(val);

	        if(isAscii)
	        {
	        	$this.css("direction", "ltr");
	        	$this.css("text-align", "left");
	        }
	        else
	        {
	            $this.css("direction", "rtl");
	            $this.css("text-align", "right");
	        }
	   	}

	});
	
	$('input[type="text"],textarea').each(function(){
	    $this = $(this);
	    if($this.val().length != 0 )
	    {
	    	var val = $this.val().charAt(0);
	    	var x =  new RegExp("[\x00-\x80]+"); // is ascii

	        //alert(x.test($this.val()));

	        var isAscii = x.test(val);

	        if(isAscii)
	        {
	        	$this.css("direction", "ltr");
	        	$this.css("text-align", "left");
	        }
	        else
	        {
	            $this.css("direction", "rtl");
	            $this.css("text-align", "right");
	        }
	    }
	    else if(typeof $this.attr('placeholder') !== 'undefined' && $this.attr('placeholder').length != 0 )
	    {
	    	var val = $this.attr('placeholder').charAt(0);
	    	var x =  new RegExp("[\x00-\x80]+"); // is ascii

	        //alert(x.test($this.val()));

	        var isAscii = x.test(val);

	        if(isAscii)
	        {
	        	$this.css("direction", "ltr");
	        	$this.css("text-align", "left");
	        }
	        else
	        {
	            $this.css("direction", "rtl");
	            $this.css("text-align", "right");
	        }
	   	}

	});
});
