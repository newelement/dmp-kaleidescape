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
    if (isPlaying) {
        isPlaying = false;
        axios
            .post('/api/stopped', { mediaSource: 'dmp-kscape' })
            .then(() => {})
            .catch(() => {});
    }
}

function getNowPlaying() {
    axios
        .get('/api/dmp-kscape-now-playing')
        .then((response) => {
            console.log(response);
            if (response.data.playing && !isPlaying) {
                let data = response.data.playing;
                let playing = {
                    mediaSource: 'dmp-kscape',
                    mediaType: 'movie',
                    poster: kscapeSettings.kscape_use_poster ? data.kscpe_poster : data.image,
                    audienceRating: data.audience_rating,
                    contentRating: data.mpaa_rating,
                    duration: data.runtime,
                };

                setKscapeNowPlaying(playing);
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
