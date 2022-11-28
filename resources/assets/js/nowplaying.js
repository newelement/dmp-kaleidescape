let kscapeSettings = {};
let isPlaying = false;

function setKscapeNowPlaying(playing) {
    isPlaying = true;
    axios
        .post('/api/now-playing', playing)
        .then(() => {})
        .catch(() => {});
}

function setKscapeStoppedPlaying() {
    isPlaying = false;
    axios
        .post('/api/stopped', { service: 'dmp-kscape' })
        .then(() => {})
        .catch(() => {});
}

function getNowPlaying() {
    axios
        .get('/api/dmp-kscape-now-playing')
        .then((response) => {
            console.log(response);
            if (response.data.playing && !isPlaying) {
                setKscapeNowPlaying(response.data.playing);
            }
        })
        .catch((response) => {
            console.log(response);
        });
}

function startKscapeSocket() {
    axios
        .get('/api/dmp-kscape-status')
        .then((response) => {
            console.log(response);
            if (response.data.status === 'playing' && !isPlaying) {
                getNowPlaying();
            } else if (response.data.status === 'stopped' && isPlaying) {
                setKscapeStoppedPlaying();
            }
        })
        .catch((response) => {
            console.log(response);
        });
}

function getKscapeSettings() {
    axios
        .get('/api/dmp-kscape-settings')
        .then((response) => {
            kscapeSettings = response.data;
            setInterval(() => {
                startKscapeSocket();
            }, 3000);
        })
        .catch((response) => {
            console.log(response);
            console.log('COULD NOT GET Kaleidescape SETTINGS');
        });
}

document.addEventListener('DOMContentLoaded', function () {
    setTimeout(() => {
        getKscapeSettings();
    }, 3000);
});
