import dayjs from "dayjs";
import { ref } from "vue";

// Number Format
export function numberFormat(amount, min_point = 2, max_point = 2) {
    const formatter = Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        useGrouping: true,
        minimumFractionDigits: min_point,
        maximumFractionDigits: max_point
    });
    return formatter.format(Number(amount));
}

// Currency Format
// export function currencyFormat(amount, min_point = 2, max_point = 2) {
//     const formatter = Intl.NumberFormat('en-NG', {
//         style: 'currency',
//         currency: 'NGN',
//         useGrouping: true,
//         minimumFractionDigits: min_point,
//         maximumFractionDigits: max_point
//     });
//     return  formatter.format(Number(amount));
// }

export function currencyFormat(amount, min_point = 2, max_point = 2) {

    const value = Number(amount);

    if (isNaN(value)) {
        return '₦0.00';
    }

    const formatter = new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        useGrouping: true,
        minimumFractionDigits: min_point,
        maximumFractionDigits: max_point
    });

    return formatter.format(value);
}

// Date Format
export function dateFormat(date, excludeTime = false) {
    let format = "DD-MMM-YYYY HH:mm:ss";
    if (excludeTime)
        format = format.split(' ')[0];
    return dayjs(date).format(format);
}


export function dateFormat2(date, excludeTime = false) {
    let format = "DD-MMM-YYYY";
    if (excludeTime)
        format = format.split(' ')[0];
    return dayjs(date).format(format);
}

// set focus
export function setFocus(input) {
    const autofocus = input.value;
    if (autofocus) {
        setTimeout(() => {
            autofocus.focus();
            autofocus.select();
        }, 5);
    }
};

// Clear reactive object
export function clearForm(obj) {
    for (const key in obj) {
        obj[key] = null;
    }
}

// date filter config
export const dateFilterConfig = ref({
    wrap: true,
    altFormat: "d-M-Y",
    altInput: true,
    dateFormat: "Y-m-d",
    enableTime: false,
    defaultHour: "00",
    time_24hr: true,
});