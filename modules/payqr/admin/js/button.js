<script>
jQuery( document ).ready(function( $ ) {
    $("li.row a").click(function(){
        var id = "#child-" + $(this).parent().attr("id");
        $(id).toggle();
    });
    $("li.row select").change(function(){
        var id = "#child-" + $(this).parent().attr("id");
        var val = $(this).val();
        if(val == 1 || val == "nonrequired"){
            $(id).show();
        }
        else {
            $(id).hide();
        }
    });
    $("li.row select").each(function(){
        var id = "#child-" + $(this).parent().attr("id");
        var val = $(this).val();
        if(val == 1 || val == "nonrequired"){
            $(id).show();
        }
    });
});
</script>