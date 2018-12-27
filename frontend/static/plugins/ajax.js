function ajax (apiName, data = {}) {
    const api = API[apiName];

    return new Promise((resolve, reject) => {
        SinriQF.api.call(api.url, data, (res) => {
            resolve(res);
        }, (error, status) => {
            // console.log(error)
            // if (typeof error.response !== 'undefined' && error.response.status === 403) {
            //     localStorage.setItem('target_href', window.location.href);
            //     window.location.href = 'login.html';
            //
            //     return;
            // }

            reject({
                error,
                status,
                message: `${apiName} Error. Feedback: ${JSON.stringify(error)} Status: ${status}`
            });
        });
    });
}
