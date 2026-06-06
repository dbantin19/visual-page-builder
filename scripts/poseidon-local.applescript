-- Poseidon Local: double-clickable start/stop controller for the local Laravel
-- dev server (php artisan serve on :8000, via PHP 8.4 / pkgx, built assets).
-- Source of truth for "Poseidon Local.app". Rebuild with:
--   osacompile -o ~/Desktop/"Poseidon Local.app" scripts/poseidon-local.applescript
--
-- Design mirrors the trident controller: the app NEVER waits on its UI thread —
-- starting is delegated to scripts/local-app-launch.sh, launched fully detached
-- so `do shell script` returns immediately; that helper waits for the server and
-- opens the browser. Both `run` and `reopen` route through showControls(), so a
-- quick Stop-then-Start double-click is never dropped. Trace -> /tmp/poseidon-app.log.

property projectRoot : "/Users/derrickbanting/Documents/claude-code/poseidon"
property serverPort : "8000"
property serverURL : "http://localhost:8000"
property logFile : "/tmp/poseidon-serve.log"
property appTitle : "Poseidon Local"

on logTrace(msg)
	try
		do shell script "printf '%s APP %s\\n' \"$(/bin/date '+%H:%M:%S')\" " & quoted form of msg & " >> /tmp/poseidon-app.log"
	end try
end logTrace

on serverIsRunning()
	set state to do shell script "/usr/sbin/lsof -nP -iTCP:" & serverPort & " -sTCP:LISTEN >/dev/null 2>&1 && echo yes || echo no"
	return (state is "yes")
end serverIsRunning

on startServer()
	-- Fire the helper fully detached: `( cmd & ) &` reparents it to launchd and
	-- lets this do-shell-script return right away.
	set helper to projectRoot & "/scripts/local-app-launch.sh"
	do shell script "( /bin/bash " & quoted form of helper & " >/dev/null 2>&1 </dev/null & ) &"
end startServer

on stopServer()
	-- Kill the `artisan serve` master (scoped to this host/port), then the
	-- listening child, then any launcher scripts. Path/port-scoped so it never
	-- touches the trident server.
	set killCmd to "pkill -f 'artisan serve --host=127.0.0.1 --port=" & serverPort & "' 2>/dev/null; "
	set killCmd to killCmd & "pids=$(/usr/sbin/lsof -nP -iTCP:" & serverPort & " -sTCP:LISTEN -t 2>/dev/null); [ -n \"$pids\" ] && kill $pids 2>/dev/null; "
	set killCmd to killCmd & "pkill -f " & quoted form of (projectRoot & "/scripts/local-serve.sh") & " 2>/dev/null; "
	set killCmd to killCmd & "pkill -f " & quoted form of (projectRoot & "/scripts/local-app-launch.sh") & " 2>/dev/null; true"
	do shell script killCmd
end stopServer

on openBrowser()
	do shell script "/usr/bin/open " & quoted form of serverURL
end openBrowser

on showControls()
	set isUp to serverIsRunning()
	logTrace("run/reopen fired; serverIsRunning=" & (isUp as text))
	if isUp then
		set choice to button returned of (display dialog "Poseidon is running at " & serverURL & "." with title appTitle buttons {"Stop Server", "Cancel", "Open in Browser"} default button "Open in Browser")
		logTrace("running-dialog button=" & choice)
		if choice is "Open in Browser" then
			openBrowser()
		else if choice is "Stop Server" then
			stopServer()
			display notification "Server stopped." with title appTitle
		end if
	else
		set choice to button returned of (display dialog "Poseidon is stopped." with title appTitle buttons {"Cancel", "Start Server"} default button "Start Server")
		logTrace("stopped-dialog button=" & choice)
		if choice is "Start Server" then
			startServer()
			logTrace("startServer fired")
			display notification "Starting Poseidon… the browser will open when it's ready (first run can take a minute)." with title appTitle
		end if
	end if
end showControls

on run
	showControls()
end run

on reopen
	showControls()
end reopen
