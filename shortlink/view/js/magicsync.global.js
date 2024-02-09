// For all kind of async magic

function awaitable(promise) {
    return promise.then(data => [data, null]).catch(err => [null, err]);
}