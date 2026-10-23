<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

<script>
document.querySelectorAll('select.port-select').forEach(function (selectElement) {
    new TomSelect(selectElement, {
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        maxOptions: 30,
        loadThrottle: 300,
        shouldLoad: function (searchText) {
            return searchText.length === 0 || searchText.length >= 2;
        },
       load: function (searchText, callback) {
    const portType = selectElement.dataset.portType;

    fetch(
        "{{ route('ports.search') }}" +
        "?q=" + encodeURIComponent(searchText) +
        "&type=" + encodeURIComponent(portType)
    )
    .then(response => response.json())
    .then(callback)
    .catch(() => callback());
}
    });
});
</script>