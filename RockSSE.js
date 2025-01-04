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
    onmessage = false;
    onerror = false;
    ondone = false;
    stopped = false;

    constructor(url) {
      this.url = url;
    }

    addCallbacks() {
      const conn = this.conn;
      conn.onmessage = (event) => {
        if (this.onmessage) this.onmessage(event);
      };
      conn.onerror = (event) => {
        if (this.onerror) this.onerror(event);
      };
    }

    log(data) {
      if (!this.debug) return;
      console.log(data);
    }

    start() {
      if (this.isRunning) return;
      this.log("starting stream ...", this.url);
      this.isRunning = true;
      this.conn = new EventSource("/rocksse" + this.url);
      this.addCallbacks();
    }

    stop() {
      if (!this.isRunning) return;
      this.log("stopping stream ...");
      this.isRunning = false;
      this.conn.close();
      if (this.stopped) this.stopped();
    }
  }

  RockSSE = new _RockSSE();
})();

// var RockSSE = {
//   streams: [],

//   /**
//    * Parse JSON from received message
//    */
//   getJSON(msg) {
//     try {
//       const json = JSON.parse(msg);
//       return json;
//     } catch (error) {
//       return false;
//     }
//   },

//   addStream(name) {
//     this.streams.push(new Stream());
//     console.log(this.streams);
//   },

//   /**
//    * Start a new SSE stream
//    */
//   start(config) {
//     const conn = new EventSource("/rocksse" + config.url);

//     const debounce = 100;
//     let messages = [];

//     // send messages
//     const sendMessages = function () {
//       messages.forEach((event) => {
//         // close connection when we get the done signal
//         if (event.data === ProcessWire.config["rocksse-done"]) {
//           // close connection
//           conn.close();

//           // trigger ondone callback if it is set
//           if (typeof config.ondone === "function") {
//             config.ondone();
//           }
//         }

//         // send message to onmessage() callback if it is set
//         if (typeof config.onmessage === "function") {
//           config.onmessage(event.data, event);
//         }
//       });
//       messages = [];
//     };
//     setInterval(sendMessages, debounce);

//     // handle messages
//     conn.onmessage = (event) => {
//       messages.push(event);
//     };

//     // handle errors
//     conn.onerror = (event) => {
//       // send error to onerror() callback if it is set
//       if (typeof config.onerror === "function") config.onerror(event);

//       // by default we log the error to the console and show an alert
//       // you can provide a custom callback via config.errorAlert
//       if (typeof config.errorAlert === "function") {
//         config.errorAlert(event);
//       } else {
//         console.error(event);
//         alert("Error - check console");
//       }
//     };

//     // return conn so the frontend can close the connection if neccessary
//     return conn;
//   },
// };

// class Stream {
//   start() {
//     return "start!";
//   }
// }
