function updateItems(r) {
    _opts.items.available = r.available;
    _opts.items.assigned = r.assigned;
    search('available');
    search('assigned');
}

$('.btn-assign').click(function () {
    let $this = $(this);
    let target = $this.data('target');
    let items = $('select.list[data-target="' + target + '"]').val();

    if (items && items.length) {
        $.post($this.attr('href'), {items: items}, function (r) {
            updateItems(r);
        });
    }
    return false;
});

$('.search[data-target]').keyup(function () {
    search($(this).data('target'));
});

function search(target) {
    let $list = $('select.list[data-target="' + target + '"]');
    $list.html('');
    let q = $('.search[data-target="' + target + '"]').val();

    let groups = {
        role: [$('<optgroup label="Роли">'), false],
        permission: [$('<optgroup label="Разрешения">'), false],
    };
    $.each(_opts.items[target], function (name, data) {
        if (data.description.indexOf(q) >= 0) {
            $('<option>').text(data.description).val(name).appendTo(groups[data.type][0]);
            groups[data.type][1] = true;
        }
    });
    $.each(groups, function () {
        if (this[1]) {
            $list.append(this[0]);
        }
    });
}

// initial
search('available');
search('assigned');