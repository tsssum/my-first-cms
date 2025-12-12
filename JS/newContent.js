$(function() {
    console.log('Привет, это новый js ))');
    initGet();
    initPost();
});


function initGet() {
    $('.ajaxArticleGet').one('click', function(){
        var contentId = $(this).attr('data-contentId');
        console.log('ID статьи = ', contentId); 
        showLoaderIdentity();
        $.get({
            url:'/ajax/showContentsHandler.php?articleId=' + contentId            
        })


        .done(function(str){
            hideLoaderIdentity();
            console.log('Ответ получен (GET)', str);
            $('.summary' + contentId).replaceWith(str);
        })


        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.log('ajaxError xhr:', xhr);
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
            console.log('Ошибка соединения при получении данных (GET)');
        });
        return false;
    });  
}





function initPost() {
    $('.ajaxArticlePost').one('click', function(){
        var content = $(this).attr('data-contentId');
        showLoaderIdentity();
        $.post({
            url: '/ajax/showContentsHandler.php', 
            data: {articleId: content}
        })


        .done(function(str){
            hideLoaderIdentity();
            console.log('Ответ получен (POST)', str);
            $('.summary' + content).replaceWith(str);
        })


        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.log('Ошибка соединения с сервером (POST)');
            console.log('ajaxError xhr:', xhr);
            console.log('ajaxError status:', status);
            console.log('ajaxError error:', error);
        });
        return false;
    });  
}