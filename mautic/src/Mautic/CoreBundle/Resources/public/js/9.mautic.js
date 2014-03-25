function loadMauticContent(route, link) {
    $.ajax({
        url: route,
        type: "GET",
        dataType: "json",
        success: function(response){
            if (response && response.newContent) {
                //update URL in address bar
                History.pushState(null, "Mautic", route);
                //get content
                $("#main-panel-content").html(response.newContent);
                $("#main-panel-breadcrumbs").html(response.breadcrumbs);

                //remove current classes from menu items
                $(".side-panel-nav").find(".current").removeClass("current");

                //remove ancestor classes
                $(".side-panel-nav").find(".current_ancestor").removeClass("current_ancestor");

                //add current class
                var parent = $(link).parent();
                $(parent).addClass("current");

                //add current_ancestor classes
                $(parent).parentsUntil("#side-panel-nav", "li").addClass("current_ancestor")

                //clear flashes
                $("#main-panel-flash-msgs").html('');
            }
        },
        error: function(request, textStatus, errorThrown) {
            alert(errorThrown);
        }
    });

    //prevent firing of href link
    $(link).attr("href", "javascript: void(0)");
}

function toggleSubMenu(link) {
    if ($(link).length) {
        //get the parent li element
        var parent = $(link).parent();
        var child = $(parent).find("ul").first();
        if (child.length) {
            var toggle = $(link).find(".subnav-toggle i");

            if (child.hasClass("subnav-closed")) {
                //open the submenu
                child.removeClass("subnav-closed").addClass("subnav-open");
                toggle.removeClass("glyphicon-plus").addClass("glyphicon-minus");
            } else {
                //close the submenu
                child.removeClass("subnav-open").addClass("subnav-closed");
                toggle.removeClass("glyphicon-minus").addClass("glyphicon-plus");
            }
        }

        //prevent firing of href link
        $(link).attr("href", "javascript: void(0)");
    }
}