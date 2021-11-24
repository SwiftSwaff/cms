function changeDivisionView(button, divisionID) {
    $(".active").removeClass("active");
    $(".viewPanel").css("display", "none");
    $(".viewPanel-elem-" + divisionID).css("display", "block");
    $(button).addClass("active");
}