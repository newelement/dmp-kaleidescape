<div class="py-4">
	<h4 class="text-2xl font-bold mb-4">Kaleidescape Settings</h4>

	<form action="/dmp-kscape/settings" method="post">
		@csrf
		@method('put')
		<div class="mb-5">
			<label for="kscape-server-url" class="block mb-2 font-bold"
				>IP Address</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-server-url"
				aria-describedby="kscape-server-urlHelp"
				name="kscape_ip_address"
				value="{{ $options['kscape_ip_address'] }}"
				required
			/>
			<div id="kscape-server-urlHelp" class="text-gray-400 text-sm">Ex: 10.0.0.32</div>
		</div>

		<div class="mb-5">
			<label for="kscape-server-port" class="block mb-2 font-bold"
				>Port</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-server-port"
				aria-describedby="kscape-server-portHelp"
				name="kscape_port"
				value="{{ $options['kscape_port'] }}"
				required
			/>
			<div id="kscape-server-portHelp" class="text-gray-400 text-sm"></div>
		</div>

		<div class="mb-5">
			<label for="kscape-socket-port" class="block mb-2 font-bold"
				>Control Protocol Device ID (CPD ID)</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-socket-port"
				aria-describedby="kscape-socket-portHelp"
				name="kscape_cpdid"
				value="{{ $options['kscape_cpdid'] }}"
                required
			/>
			<div id="kscape-socket-portHelp" class="text-gray-400 text-sm"></div>
		</div>

		<div class="mb-5">
			<label for="kscape-username" class="block mb-2 font-bold"
				>Passcode</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-username"
				aria-describedby="kscape-usernameHelp"
				name="kscape_passcode"
				value="{{ $options['kscape_passcode'] }}"
			/>
			<div id="kscape-usernameHelp" class="text-gray-400 text-sm">Optional. Only if you have a passcode setup in Kaleidescape</div>
		</div>

        <div class="mb-5">
            <label for="kscape-useposter" class="block mb-2 font-bold flex items-center"
                >
            <input
                type="checkbox"
                id="kscape-useposter"
                aria-describedby="kscape-useposterHelp"
                name="kscape_use_poster"
                value="1" @checked(old('kscape_use_poster', $options['kscape_use_poster']))
            /> <span class="ml-2">Use Kaleidescape's Posters over TMDB</span>
            </label>
            <div id="kscape-useposterHelp" class="text-gray-400 text-sm">Use the poster images from the Kaleidescape device over the TMDB poster images.</div>
        </div>

        <!--
		<div class="mb-5">
			<label for="kscape-password" class="block mb-2 font-bold"
				>Use SSL for IP Address</label
			>
			<input
				type="password"
				class="w-full mb-2"
				id="kscape-password"
				aria-describedby="kscape-passwordHelp"
				name="kscape_use_ssl"
				value="{{ $options['kscape_use_ssl'] }}"
			/>
			<div id="kscape-passwordHelp" class="text-gray-400 text-sm"></div>
		</div>-->

		<button type="submit" class="btn-primary">Save</button>
	</form>
</div>
