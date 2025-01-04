var RockSSE = {
  /**
   * Debounce function to limit the rate at which a function can fire
   * @param {Function} func The function to debounce
   * @param {number} wait The number of milliseconds to delay
   * @returns {Function} A debounced version of the passed function
   */
  debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
      const later = () => {
        clearTimeout(timeout);
        func(...args);
      };
      clearTimeout(timeout);
      timeout = setTimeout(later, wait);
    };
  },

  /**
   * Parse JSON from received message
   */
  getJSON(msg) {
    try {
      const json = JSON.parse(msg);
      return json;
    } catch (error) {
      return false;
    }
  },

  /**
   * Start a new SSE stream
   */
  start(config) {
    const conn = new EventSource("/rocksse" + config.url, {
      withCredentials: true,
    });

    // handle messages
    conn.onmessage = (event) => {
      // close connection when we get the done signal
      if (event.data === ProcessWire.config["rocksse-done"]) {
        // close connection
        conn.close();

        // trigger ondone callback if it is set
        if (typeof config.ondone === "function") {
          config.ondone();
        }
      }

      // send message to onmessage() callback if it is set
      if (typeof config.onmessage === "function") {
        config.onmessage(event.data, event);
      }
    };

    // handle errors
    conn.onerror = (event) => {
      // send error to onerror() callback if it is set
      if (typeof config.onerror === "function") config.onerror(event);

      // by default we log the error to the console and show an alert
      // you can provide a custom callback via config.errorAlert
      if (typeof config.errorAlert === "function") {
        config.errorAlert(event);
      } else {
        console.error(event);
        alert("Error - check console");
      }
    };

    // return conn so the frontend can close the connection if neccessary
    return conn;
  },
};
