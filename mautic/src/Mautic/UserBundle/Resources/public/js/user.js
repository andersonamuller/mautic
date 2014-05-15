//UserBundle
Mautic.userOnLoad = function (container) {
    if ($(container + ' form[name="user"]').length) {
        if ($('#user_role_lookup').length) {
            var roles = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('label'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                prefetch: {
                    url: mauticBaseUrl + "ajax?ajaxAction=user:user:rolelist"
                },
                remote: {
                    url: mauticBaseUrl + "ajax?ajaxAction=user:user:rolelist&filter=%QUERY"
                },
                dupDetector: function (remoteMatch, localMatch) {
                    return (remoteMatch.label == localMatch.label);
                },
                ttl: 1800000,
                limit: 5
            });
            roles.initialize();
            roles.clearPrefetchCache();
            $("#user_role_lookup").typeahead(
                {
                    hint: true,
                    highlight: true,
                    minLength: 2
                },
                {
                    name: 'user_role',
                    displayKey: 'label',
                    source: roles.ttAdapter()
                }).on('typeahead:selected', function (event, datum) {
                    $("#user_role").val(datum["value"]);
                }).on('typeahead:autocompleted', function (event, datum) {
                    $("#user_role").val(datum["value"]);
                }
            );
        }
        if ($('#user_position').length) {
            var positions = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                prefetch: {
                    url: mauticBaseUrl + "ajax?ajaxAction=user:user:positionlist"
                },
                remote: {
                    url: mauticBaseUrl + "ajax?ajaxAction=user:user:positionlist&filter=%QUERY"
                },
                dupDetector: function (remoteMatch, localMatch) {
                    return (remoteMatch.label == localMatch.label);
                },
                ttl: 1800000,
                limit: 5
            });
            positions.initialize();
            $("#user_position").typeahead(
                {
                    hint: true,
                    highlight: true,
                    minLength: 2
                },
                {
                    name: 'user_position',
                    displayKey: 'value',
                    source: positions.ttAdapter()
                }
            );
        }
    } else {
        if ($(container + ' #list-search').length) {
            Mautic.activateSearchAutocomplete('list-search', 'user');
        }
    }
};

Mautic.roleOnLoad = function (container) {
    if ($(container + ' #list-search').length) {
        Mautic.activateSearchAutocomplete('list-search', 'role');
    }
};


/**
 * Toggles permission panel visibility for roles
 */
Mautic.togglePermissionVisibility = function () {
    //add a very slight delay in order for the clicked on checkbox to be selected since the onclick action
    //is set to the parent div
    setTimeout(function () {
        if ($('#role_isAdmin_0').prop('checked')) {
            $('#permissions-container').removeClass('hide');
        } else {
            $('#permissions-container').addClass('hide');
        }
    }, 10);
};

Mautic.onPermissionChange = function (container, event, bundle) {
    //add a very slight delay in order for the clicked on checkbox to be selected since the onclick action
    //is set to the parent div
    setTimeout(function () {
        var granted = 0;
        var clickedBox = $(event.target).find('input:checkbox').first();
        if ($(clickedBox).prop('checked')) {
            if ($(clickedBox).val() == 'full') {
                //uncheck all of the others
                $(container).find("label input:checkbox:checked").map(function () {
                    if ($(this).val() != 'full') {
                        $(this).prop('checked', false);
                        $(this).parent().toggleClass('active');
                    }
                })
            } else {
                //uncheck full
                $(container).find("label input:checkbox:checked").map(function () {
                    if ($(this).val() == 'full') {
                        granted = granted - 1;
                        $(this).prop('checked', false);
                        $(this).parent().toggleClass('active');
                    }
                })
            }
        }

        //update granted numbers
        if ($('.' + bundle + '_granted').length) {
            mauticVars.showLoadingBar = false;
            $.ajax({
                url: mauticBaseUrl + "ajax?ajaxAction=user:role:permissionratio&bundle=" + bundle,
                type: "POST",
                data: $('form[name="role"]').serialize(),
                dataType: "json",
                success: function (response) {
                    if (response.granted) {
                        $('.' + bundle + '_granted').html(response.granted);
                    }
                },
                error: function (request, textStatus, errorThrown) {
                    alert(errorThrown);
                }
            });
        }
    }, 10);
};
