export const objectToAlphaSortedArray = function (obj, sortProp) {
    return Object.entries(obj)
        .sort(([k_a, v_a],[k_b,v_b]) => {
            const hello = 'hi';
            return v_a[sortProp].toLowerCase() > v_b[sortProp].toLowerCase() ? 1 :  v_a[sortProp].toLowerCase() < v_b[sortProp].toLowerCase() ? -1 : 0
        })
}