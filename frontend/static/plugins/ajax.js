function ajax (apiName, data = {}) {
    const api = API[apiName];

    return new Promise((resolve, reject) => {
        SinriQF.api.call(api.url, data, (res) => {
            resolve(res);
        }, (error, status) => {
            const feedback = typeof error.response !== 'undefined' && error.response.data
            // console.log(error)
            // if (typeof error.response !== 'undefined' && error.response.status === 403) {
            //     localStorage.setItem('target_href', window.location.href);
            //     window.location.href = 'login.html';
            //
            //     return;
            // }

            console.log(JSON.stringify(error, null, 4))

            reject({
                error,
                status,
                message: `${apiName} Error. Feedback: ${JSON.stringify(feedback, null, 4)} Status: ${status}`
            });
        });
    });
}
