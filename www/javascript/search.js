$(function() {

$('#btnSearch').click(function() {
//这里调用最关键的Search方法，取数据
    Search();
});

//申请的APP ID，这里换成你自己的。
var AppId = "AppId=31F3C13DC5D41C42D4A18F9E04DE1DEA73762186";
//通过用户输入搜索词获得检索串
var Query = "Query="
//指定检索来源类型，Bing提供了网页、视频、图片等所有类型，参考API
//这里指定的是网页类型
var Sources = "Sources=Web";
//指定API版本
var Version = "Version=2.0";
//指定所在地区，如google，每个地区搜索结果是不一样的，这里指定中国
var Market = "Market=zh-cn"; 
//一些选项设置，这里开启搜索结果中的搜索词高亮
var Options = "Options=EnableHighlighting";
//每页返回条数
var WebCount = 10;
//当前为第几页，从0开始的
var WebOffset = 0;

function Search() {
 
//获取用户输入的搜索词，并替换空格为“+”
        var searchTerms = $('#txtQuery').val().replace(" ", "+");
 
//防止传输中文时产生乱码
        searchTerms = encodeURI(searchTerms);   
 
//将接口需要的所有参数封装为数组
        var arr = [AppId, Query + searchTerms, Sources, Version, Market, Options, "Web.Count=" + WebCount, "Web.Offset=" + WebOffset, "JsonType=callback", "JsonCallback=?"];
 
//将参数数组拼装成url串，最终得到bing的URL Service的请求URL
        var requestStr = "http://api.search.live.net/json.aspx?" + arr.join("&");
 
//通过jquery ajax调用bing json数据接口
        $.ajax({
            type: "GET",
            url: requestStr,
 //指定数据类型为jsonp
            dataType: "jsonp",  
 
//调用成功后返回数据对象，并调用处理方法
            success: function(msg) {
                SearchCompleted(msg);
            },
            error: function(msg) {
                alert("Something hasn't worked\n" + msg.d);
            }
        });
    }

 
function SearchCompleted(response) {
//检查结果数据对象中的Errors对象，判断是否发生错误
    var errors = response.SearchResponse.Errors;
    if (errors != null) {
        // 发生错误的话调用错误信息显示方法
        DisplayErrors(errors);
    }
    else {
        // 没有错误的话调用结果信息显示方法
        DisplayResults(response);
    }
}

function DisplayResults(response) {
//清空结果列表
    $("#result-list").html("");  
//清空翻页导航
    $("#result-navigation li").filter(".nav-page").remove();   
// 清空结果描述信息
    $("#result-aggregates").children().remove(); 

//获取结果数据对象
    var results = response.SearchResponse.Web.Results;  

//描述信息部分，即总过多少条，当前是哪些条
    $('#result-aggregates').prepend("<p>检索词： " + response.SearchResponse.Query.SearchTerms + "</p>");
    $('#result-aggregates').prepend("<p id=\"result-count\">当前显示 " + StartOffset(results)
        + " 至 " + EndOffset(results)
        + "&nbsp;&nbsp;总共:" + parseInt(response.SearchResponse.Web.Total) + "</p>");

//创建结果列表，把每一项要显示的内容放在一个数组中
    var link = [];  
//因为开启了搜索词高亮选项，这里进行高亮匹配
    var regexBegin = new RegExp("\uE000", "g");    
    var regexEnd = new RegExp("\uE001", "g");     
    for (var i = 0; i < results.length; ++i) {
//创建每一结果项的信息
        link[i] = "<li><a href=\"" + results[i].Url + "\" title=\"" + results[i].Title + "\">"
            + results[i].Title + "</a>"
            + "<p>" + results[i].Description + "<p>"
            + "<p class=\"result-url\">" + results[i].Url + "</p></li>";

//搜索词加粗显示
        link[i] = link[i].replace(regexBegin, "<strong>").replace(regexEnd, "</strong>");
    }
//在页面结果区域显示结果列表    
   $("#result-list").html(link.join('')); 

//处理导航区域
    CreateNavigation(response.SearchResponse.Web.Total, results.length);
}

});
