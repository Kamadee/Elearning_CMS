function onlyNumberAmount(event) {
  // Lấy giá trị hiện tại của input
  let value = event.target.value;

  // Loại bỏ tất cả các ký tự không phải là số
  value = value.replace(/[^0-9]/g, '');

  // Định dạng lại giá trị thành dạng tiền Việt Nam đồng
  if (value !== '') {
    const formatter = new Intl.NumberFormat('vi-VN', {
      style: 'currency',
      currency: 'VND',
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    });
    value = formatter.format(value);
  }

  // Cập nhật lại giá trị của input
  event.target.value = value;
}

$.validator.addMethod(
  "greaterThan",
  function(value, element, params) {
    console.log("ppp", value, params)
      let otherField = $(params);
      let otherVal = otherField.val();
      if (!otherVal) otherVal = '0';
      if (!value) value = '0';

      let amountValue = value.replace(/[^0-9]/g, '');
      let amountOtherValue = otherVal.replace(/[^0-9]/g, '');

      amountValue = parseInt(amountValue) || 0;
      amountOtherValue = parseInt(amountOtherValue) || 0;

      return amountValue > amountOtherValue;
  },
  "Value must be greater than {0}."
);

$.validator.addMethod(
  "lessThan",
  function(value, element, params) {
      let otherField = $(params);
      let otherVal = otherField.val();
      if (!otherVal) otherVal = '0';
      if (!value) value = '0';

      let amountValue = value.replace(/[^0-9]/g, '');
      let amountOtherValue = otherVal.replace(/[^0-9]/g, '');

      amountValue = parseInt(amountValue) || 0;
      amountOtherValue = parseInt(amountOtherValue) || 0;

      return amountValue < amountOtherValue;
  },
  "Value must be greater than {0}."
);

$.validator.addMethod(
  "minPrice",
  function(value, element, params) {
    console.log("222", value, parseFloat(value), parseFloat(params))
    return parseFloat(value) >= parseFloat(params)
  },
  "Value must be greater than {0}."
);
