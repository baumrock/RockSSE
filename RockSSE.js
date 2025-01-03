var RockSSE = {
  /**
   * Start a new SSE stream
   */
  start(config) {
    const conn = new EventSource(config.url, { withCredentials: true });

    // send message to onmessage() callback if it is set
    if (typeof config.onmessage === "function") {
      conn.onmessage = (event) => {
        config.onmessage(event.data, event);
      };
    }

    // return conn so the frontend can close the connection if neccessary
    return conn;
  },
};
