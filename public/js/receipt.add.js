let cloneProductRow = '';
$(document).ready(function() {
    if (window.location.pathname == 'receipt/create') {
        cloneProductRow = $('#listProduct').html();
    } else {
        cloneProductRow = '<tr><td></td><td></td><td></td><td></td><td>'+
            '<select class="form-control select-course select2-group" style="width: 100%;" tabindex="-1" aria-hidden="true"></select>'+
            '</td><td><div class="input-group"><div class="input-group-prepend"><span class="input-group-text">¥</span></div>'+
            '<input type="number" min="0" class="form-control"></div></td><td><div class="input-group"><input type="number" min="0" class="form-control"></div>'+
            '</td><td></td></tr>'
    }
    $('.select-sub-category').select2({placeholder: 'サブカテゴリー選択'});
    $('.select-product').select2({placeholder: '商品選択'});
    $('.select2-group').select2();
    $('.select-items-search').on('change', function () {
        $.ajax({
            url : "/receipt/search_product_list",
            type : "get",
            data : {
                category: $('.select-category').val(),
                sub_category: $('.select-sub-category').val(),
                type: this.getAttribute('data-type')
            }
        }).done(function(data) {
            reloadSelect(data);
        });
    });

    $('#reason_payment').on('change', function () {
        if ($("#reason_payment :selected").text() == 'Other') {
            $("#reason_other").removeAttr('disabled');
        } else {
            $("#reason_other").prop("disabled", true);
            $("#reason_other").val('');
        }
    });

    $('#add-product').on('click', function () {
        let myList = [];
        if ($('.select-product option:selected').length == 0) {
            return;
        }

        $('.select-product option:selected').each(function(){
            myList.push({
                "prdValue": $(this).val(),
                "prdName": $(this).text(),
                "courses": JSON.parse($(this).attr("data-course"))
            });
        });

        addProductToList(myList);
    });

    $(document).on("click", ".remove-product" , function() {
        $(this).parents('tr').remove();
        updateListProductNo();
        updateAfterRemove();
    });

    $(document).on("change", ".select-course" , function() {
        let price = $(this).find('option:selected').attr('data-price');
        $(this).parents('tr').find('.product-price').val(price);
    });

    $(document).on('click', '.radio_payment', function () {
        $(this).find("[name='payment_method']").prop("checked", true);
    });

    $(document).on('click', '#submit_add_product', function (e) {
        let errorMessages = new Array();
        let checkName = $('#customer_name').val();
        if (checkName == "") {
            errorMessages.push('患者名の入力は必須です。');
        }

        let checkMethod = $("input[name='payment_method']:checked").val()
        if (checkMethod == undefined) {
            errorMessages.push('支払い方法の指定は必須です。');
        }

        let checkElementTd = $("#listProduct").children('tr:first');
        if (checkElementTd.children('td:last').html() == "") {
            errorMessages.push('商品の指定は必須です。');
        }

        let checkReason = $("#reason_payment").val();
        if (checkReason == "") {
            errorMessages.push('但し書きの記載は必須です。');
        }

        let prdPrice = $('input[name="product-price[]"]').map(function(){return $(this).val();}).get();
        let validPrice = true;
        $(prdPrice).each(function(index) {
            if (prdPrice[index] < 0) {
                validPrice = false;
            }
        })
        if (!validPrice) {
            errorMessages.push('商品の金額は0より大きくなければなりません。');
        }

        let prdQuantity = $('input[name="product-quantity[]"]').map(function(){return $(this).val();}).get();
        let validQuantity = true;
        $(prdQuantity).each(function(index) {
            if (prdQuantity[index] < 0) {
                validQuantity = false;
            }
        })
        if (!validQuantity) {
            errorMessages.push('商品の数量は0より大きくなければなりません。');
        }

        if (errorMessages.length > 0) {
            showAlertMessages(errorMessages, '#alert-messages');
            $(window).scrollTop(0);
            return false;
        }
    });
});

function reloadSelect(data) {
    let htmlProduct = '';
    let htmlSubCategory = '<option value="">Select Sub Category</option>';
    let products = data.data.products;
    if (data.data.type == 'category') {
        let subCategories = data.data.subCategories
        for(let i = 0; i < subCategories.length; i++){
            htmlSubCategory += '<option value="' + subCategories[i].id + '">' + subCategories[i].name + '</option>';
        }

        $('.select-sub-category').find('option').remove().end().append(htmlSubCategory);
    }

    for(let j = 0; j < products.length; j++){
        htmlProduct += "<option data-course='" + JSON.stringify(products[j].courses) + "' value='" + products[j].id + "'>" + products[j].name + "</option>";
    }

    $('.select-product').find('option').remove().end().append(htmlProduct);
}

function addProductToList(listProduct) {
    let firstTdValue = $("#listProduct").children('tr:first');
    if (firstTdValue.children('td:last').html() == "") {
        firstTdValue.remove();
    }

    let htmlProduct = '';
    for (let i = 0; i < listProduct.length; i++) {
        htmlProduct += '<tr><td class="text-center order-product">'
            + '</td><td class="text-center">'
            + $('.select-category :selected').text()
            + '</td><td class="text-center">'
            + setTextSubCategory()
            + '<input type="hidden" name="sub-category[]" value="'
            + setTextSubCategory()
            + '"> </td><td class="text-center">'
            + listProduct[i].prdName
            + '<input type="hidden" name="product-id[]" value="' + listProduct[i].prdValue + '">'
            + '</td><td class="text-center">'
            + '<select required class="form-control select-course" name=courses[] tabindex="-1" aria-hidden="true">'
            + getProductCourseList(listProduct[i].courses)
            + '</select> </td><td>'
            + '<div class="input-group"> <div class="input-group-prepend"> <span class="input-group-text">¥</span> </div> <input value="'
            + getFirstCoursePrice(listProduct[i].courses)
            + '" type="number" min="0" class="product-price form-control" required name="product-price[]"> </div>'
            + ' </td><td class="text-center">'
            + '<div class="input-group"> <input type="number" min="0" class="form-control" value="1" required name="product-quantity[]"> </div>'
            + ' </td><td class="text-center">'
            + '<button class="btn btn-danger remove-product"><i class="fas fa-trash-alt"></i></button> </td></tr>'
    }

    $('#listProduct').append(htmlProduct);
    $('.select-course').select2();
    updateListProductNo();
    resetSelectProductData();
}

function getProductCourseList(courses) {
    let hrmlcource = '';
    for (let i = 0; i < courses.length; i++) {
        hrmlcource += '<option data-price="' + courses[i].price
            + '" value="' + courses[i].id + '">' 
            + courses[i].course_name + '</option>';
    }

    return hrmlcource;
}

function updateListProductNo() {
    let element = $('#listProduct>tr');
    let length = element.length;

    for (let i = 0; i < length; i++) {
        $(element[i]).find('.order-product').html((i + 1));
    }
}

function setTextSubCategory() {
    return $('.select-sub-category :selected').val() == '' ? ' ' : $('.select-sub-category :selected').text();
}

function resetSelectProductData() {
    $(".select-category").val("").change();
    $('.select-sub-category').find('option').remove();
    $('.select-product').find('option').remove();
}

function getFirstCoursePrice(course) {
    return course[0].price;
}

function updateAfterRemove() {
    let listProductLength = $('#listProduct>tr').length;
    if (listProductLength == 0) {
        $('#listProduct').append(cloneProductRow);
    }
}

function showAlertMessages(data, object) {
    if ($.isArray(data)) {
        if (data.length > 0) {
            var htmlError = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Error!</strong></br>';
            var countMessage = 0;
            data.forEach(function (element) {
                countMessage++;
                htmlError += '<p>' + countMessage + '. ' + element + '</p>';
            });
            htmlError += '<button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">&times;</span> </button> </div>';
            $(object).html('');
            $(object).append(htmlError);
        }
    }
}
