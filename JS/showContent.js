$(function(){
    
    console.log('Привет, это страый js ))');
    init_get();
    init_post();
});

function init_get() 
{
    $('a.ajaxArticleBodyByGet').one('click', function(e){
        e.preventDefault();
        
        var contentId = $(this).attr('data-contentId');
        console.log('GET запрос для статьи ID = ', contentId); 
        
        // Правильный путь - без начального слеша, если проект в подпапке
        var url = 'ajax/showContentsHandler.php?articleId=' + contentId;
        console.log('URL запроса:', url);
        
        showLoaderIdentity();
        
        $.ajax({
            url: url, 
            dataType: 'json',
            method: 'GET',
            cache: false // Отключаем кэширование для отладки
        })
        .done(function(response){
            hideLoaderIdentity();
            console.log('GET ответ получен:', response);
            
            if (response.status === 'success' && response.content) {
                // Заменяем содержимое элемента
                var summaryElement = $('.summary' + contentId);
                if (summaryElement.length) {
                    summaryElement.html(response.content);
                    console.log('Контент успешно заменен');
                } else {
                    console.error('Элемент .summary' + contentId + ' не найден');
                }
            } else if (response.error) {
                console.error('Ошибка от сервера:', response.error);
                $('.summary' + contentId).html('<span style="color: red;">Ошибка: ' + response.error + '</span>');
            }
        })
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.error('GET AJAX ошибка:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response text:', xhr.responseText);
            
            $('.summary' + contentId).html('<span style="color: red;">Ошибка загрузки содержимого</span>');
        });
        
        return false;
    });  
}

function init_post() 
{
    $('a.ajaxArticleBodyByPost').one('click', function(e){
        e.preventDefault();
        
        var contentId = $(this).attr('data-contentId');
        console.log('POST запрос для статьи ID = ', contentId);
        
        showLoaderIdentity();
        
        $.ajax({
            url: 'ajax/showContentsHandler.php', 
            dataType: 'json',
            data: {articleId: contentId},
            method: 'POST'
        })
        .done(function(response){
            hideLoaderIdentity();
            console.log('POST ответ получен:', response);
            
            if (response.status === 'success' && response.content) {
                var summaryElement = $('.summary' + contentId);
                if (summaryElement.length) {
                    summaryElement.html(response.content);
                    console.log('Контент успешно заменен (POST)');
                }
            } else if (response.error) {
                console.error('Ошибка от сервера (POST):', response.error);
                $('.summary' + contentId).html('<span style="color: red;">Ошибка: ' + response.error + '</span>');
            }
        })
        .fail(function(xhr, status, error){
            hideLoaderIdentity();
            console.error('POST AJAX ошибка:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response text:', xhr.responseText);
            
            $('.summary' + contentId).html('<span style="color: red;">Ошибка загрузки содержимого</span>');
        });
        
        return false;
    });  
}