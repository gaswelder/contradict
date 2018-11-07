const getJSON = url => fetch(url).then(r => r.json());
const token = "bed04814f428bf40ef0e";

export default {
  dicts() {
    return getJSON(`http://localhost:8080?token=${token}`);
  },

  test(dictID) {
    return getJSON(`http://localhost:8080/${dictID}/test?token=${token}`);
  },

  submitAnswers(dictID, entries) {
    const data = entries.map(([k, v]) => `${k}=${v}`).join("&");
    return fetch(`http://localhost:8080/${dictID}/test?token=${token}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: data
    }).then(r => r.json());
  },

  entry(id) {
    return getJSON(`http://localhost:8080/entries/${id}?token=${token}`);
  },

  updateEntry(id, entry) {
    return fetch(`http://localhost:8080/entries/${id}?token=${token}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `q=${entry.q}&a=${entry.a}`
    });
  }
};
