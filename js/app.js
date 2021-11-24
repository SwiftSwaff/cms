var mqSmall = window.matchMedia('(max-width: 900px)');
var mqLarge = window.matchMedia('(min-width: 901px)');

$("document").ready(function(){
    $(".gallery-elem picture img.gallery-img").click(function() {
        $("body").addClass("noscroll");
        var image = $(this).clone();
        image.removeAttr("height");
        $("#overlayWrapper").append(image);
        $("#pageOverlay").css("display", "flex");
    });
    
    $("#pageOverlay .closeBtn").click(function() {
        $("body").removeClass("noscroll");
        $("#pageOverlay").css("display", "none");
        $("#overlayWrapper img.gallery-img").remove();
    });
    
    $(".loadNewsBtn").click(function() {
        var newsIdx = parseInt($("#newsIdx").val());
        var newsNum = parseInt($("#newsNum").val());
        
        $.ajax({
            url: "",
            data: {
                "newsPostIdx" : newsIdx,
                "newsPostNum" : newsNum
            },
            type: "POST",
            success: function(result) {
                if (!result) {
                    $(".loadNewsBtn").remove();
                    $("#loadNewsArea").append("<span>- End of Posts -</span>");
                }
                else {
                    $("main article:last").after(result);
                    $("#loadNewsArea").detach().appendTo("main");
                    $("#newsIdx").val(newsIdx + newsNum);
                }
            }
        })
    });
    
    function removeNavActiveState(e) {
        if (e.matches) {
            $('ul.navbar-container').removeClass('active');
            $('ul.navbar-container').find('ul').removeClass('active');
        }
    }

    $('.navbar-elem a').click(function() {
        if (mqSmall.matches) {
            var dropdown = $(this).next();
            if (dropdown.hasClass("navbar-dropdown")) {
                if (!dropdown.hasClass('active')) {
                    if (!(dropdown.parent().parent().hasClass("active") && dropdown.parent().parent().hasClass("navbar-dropdown"))) {
                        $('ul.navbar-container').find('ul').removeClass('active');
                    }
                    else {
                        dropdown.parent().parent().find('ul').removeClass('active');
                    }
                    dropdown.addClass('active');
                }
                else {
                    dropdown.removeClass('active');
                    dropdown.find('ul').removeClass('active');
                }
            }
        }
    });

    $('.navbar-ham').click(function() {
        if (mqSmall.matches) {
            if (!$('ul.navbar-container').hasClass('active')) {
                $('ul.navbar-container').addClass('active');
            }
            else {
                $('ul.navbar-container').removeClass('active');
                $('ul.navbar-container').find('ul').removeClass('active');
            }
        }
    });

    mqLarge.addEventListener("change", removeNavActiveState);
});