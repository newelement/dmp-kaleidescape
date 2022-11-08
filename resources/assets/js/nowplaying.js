let kscapeSettings = {};
let kscapePlaying = false;

function setKscapeNowPlaying(playing) {
    axios
        .post('/api/now-playing', playing)
        .then(() => {})
        .catch(() => {});
}

function getKscapeNowPlaying() {
    axios
        .get('/api/dmp-kscape-now-playing')
        .then((response) => {
            let playing = {
                poster: decodeURIComponent(
                    response.data[1].result.item.art.poster.replace('image://', '').slice(0, -1)
                ),
                contentRating: response.data[0].result.item.mpaa.replace('Rated ', ''),
                audienceRating: response.data[0].result.item.rating,
                duration: response.data[0].result.item.runtime / 60,
                mediaType: 'movie',
            };

            setKscapeNowPlaying(playing);
        })
        .catch(() => {});
}

function setKscapeStoppedPlaying() {
    kscapePlaying = false;
    axios
        .post('/api/stopped')
        .then(() => {})
        .catch(() => {});
}

function startKscapeSocket() {
    const socket = new WebSocket(
        'ws://' + kscapeSettings.kscape_url + ':' + kscapeSettings.kscape_socket_port
    );

    socket.addEventListener('open', () => {});

    socket.addEventListener('message', (event) => {
        const data = JSON.parse(event.data);
        if (data.method === 'Player.OnPlay' && data.params.data.item.type === 'movie') {
            if (!kscapePlaying) {
                getKscapeNowPlaying();
            }
            kscapePlaying = true;
        }

        if (data.method === 'Player.OnStop' && data.params.data.item.type === 'movie') {
            kscapePlaying = false;
            setKscapeStoppedPlaying();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    axios
        .get('/api/dmp-kscape-settings')
        .then((response) => {
            kscapeSettings = response.data;
            startKscapeSocket();
        })
        .catch((response) => {
            console.log(response);
            console.log('COULD NOT GET KODI SETTINGS');
        });
});
