<div class="py-4">
	<h4 class="text-2xl font-bold mb-4">Kscape Settings</h4>

	<form action="/dmp-kscape/settings" method="post">
		@csrf
		@method('put')
		<div class="mb-5">
			<label for="kscape-server-url" class="block mb-2 font-bold"
				>Kscape Server URL</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-server-url"
				aria-describedby="kscape-server-urlHelp"
				name="kscape_url"
				value="{{ $options['kscape_url'] }}"
				required
			/>
			<div id="kscape-server-urlHelp" class="text-gray-400 text-sm">Ex: localhost, 10.0.0.32</div>
		</div>

		<div class="mb-5">
			<label for="kscape-server-port" class="block mb-2 font-bold"
				>Kscape Server Port</label
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
				>Kscape Socket Port</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-socket-port"
				aria-describedby="kscape-socket-portHelp"
				name="kscape_socket_port"
				value="{{ $options['kscape_socket_port'] }}"
			/>
			<div id="kscape-socket-portHelp" class="text-gray-400 text-sm"></div>
		</div>

		<div class="mb-5">
			<label for="kscape-username" class="block mb-2 font-bold"
				>Kscape Username</label
			>
			<input
				type="text"
				class="w-full mb-2"
				id="kscape-username"
				aria-describedby="kscape-usernameHelp"
				name="kscape_username"
				value="{{ $options['kscape_username'] }}"
			/>
			<div id="kscape-usernameHelp" class="text-gray-400 text-sm">Optional. Only if you have setup authentication.</div>
		</div>

		<div class="mb-5">
			<label for="kscape-password" class="block mb-2 font-bold"
				>Kscape Password</label
			>
			<input
				type="password"
				class="w-full mb-2"
				id="kscape-password"
				aria-describedby="kscape-passwordHelp"
				name="kscape_password"
				value="{{ $options['kscape_password'] }}"
			/>
			<div id="kscape-passwordHelp" class="text-gray-400 text-sm">Optional. Only if you have setup authentication.</div>
		</div>

		<button type="submit" class="btn-primary">Save</button>
	</form>
</div>
