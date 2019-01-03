function ajax (apiName, data = {}) {
    const api = API[apiName];

    return new Promise((resolve, reject) => {
        SinriQF.api.call(api.url, data, (res) => {
            resolve(res);
        }, (error, status) => {
            // be has response
            if (status !== -1) {
                console.log(JSON.stringify(error, null, 4))

                reject({
                    error,
                    status,
                    message: `[${apiName}] Error. Feedback: ${error.error}`
                });
            } else {
                reject({
                    error,
                    status,
                    message: `[${apiName}] Network Error. Status: ${error.response.status} ${error.response.statusText}`
                });
            }
        });
    });
}
