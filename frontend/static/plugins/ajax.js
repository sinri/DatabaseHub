function ajax (apiName, data = {}, cancelExecutor = (c) => c) {
    const api = API[apiName];
    let url = SinriQF.config.ApiBase + api.url;

    if (typeof api.suffix !== 'undefined') {
        url += `/${data[api.suffix]}`
    }

    return new Promise((resolve, reject) => {
        const CancelToken = axios.CancelToken;

        data.token = SinriQF.api.getTokenFromCookie();

        axios.post(url, data, {
            cancelToken: new CancelToken(cancelExecutor)
        }).then((response) => {
            if (response.status !== 200 || !response.data) {
                callbackForError(response.data, response.status);
                return;
            }

            const body = response.data;

            if (typeof body.code !== 'undefined' && body.code === 'OK') {
                resolve(body.data)
            } else {
                const error = body.data ? body.data : 'Unknown Error'

                reject({
                    error,
                    status: response.status,
                    message: `[${apiName}] Error. Feedback: ${error.error || error}`
                });
            }
        }).catch((error) => {
            if (axios.isCancel(error)) return;

            reject({
                error,
                status: -1,
                message: `[${apiName}] Network Error. Status: ${error.response.status} ${error.response.statusText}`
            });
        });
    });
}
