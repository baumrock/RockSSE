var RockSSE;
(() => {
  class _RockSSE {
    streams = {};

    addStream(url) {
      const stream = new Stream(url);
      this.streams[url] = stream;
      console.log("added stream", stream);
      return stream;
    }
  }

  class Stream {
    conn = false;
    debug = false;
    isRunning = false;
    url = false;

    // callbacks for the stream
    errorAlert = false;
    onmessage = false;
    onerror = false;
    ondone = false;
    onstart = false;
    onstop = false;
    onstarted = false;
    onstopped = false;

    constructor(url) {
      this.url = url;
    }

    addCallbacks() {
      const conn = this.conn;
      conn.onmessage = (event) => {
        // close connection when we get the done signal
        if (event.data === ProcessWire.config["rocksse-done"]) {
          this.stop();
          if (this.ondone) this.ondone();
          return;
        }
        if (this.onmessage) this.onmessage(event);
      };

      // error handler
      conn.onerror = (event) => {
        // send error to onerror() callback if it is set
        if (this.onerror) this.onerror(event);

        // by default we log the error to the console and show an alert
        // you can provide a custom callback via config.errorAlert
        if (this.errorAlert) {
          this.errorAlert(event);
        } else {
          console.error(event);
          alert("Error - check console");
        }
      };
    }

    log(data) {
      if (!this.debug) return;
      console.log(data);
    }

    message(msg) {
      if (!this.onmessage) return;
      this.onmessage({ data: msg });
    }

    getJSON(msg) {
      try {
        return JSON.parse(msg);
      } catch (error) {
        return false;
      }
    }

    start() {
      // always fire the onstart event (even if the stream is running)
      // this is in case one wants to provide user feedback like an alert
      // that indicates that the stream is running and can not be started again
      if (this.onstart) this.onstart();

      if (this.isRunning) return;
      this.log("starting stream ...", this.url);
      this.isRunning = true;
      this.conn = new EventSource("/rocksse" + this.url);
      this.addCallbacks();
      if (this.onstarted) this.onstarted();
    }

    stop() {
      // always fire the onstop callback - see notes in start()
      if (this.onstop) this.onstop();

      if (!this.isRunning) return;
      this.log("stopping stream ...");
      this.isRunning = false;
      if (this.conn) {
        this.conn.close();
        this.conn = null;
      }
      if (this.onstopped) this.onstopped();
    }
  }

  RockSSE = new _RockSSE();
})();
