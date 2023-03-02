import React, { useState } from "react";
import api from "./api";

export default function Export() {
  const handleSubmit = async (e) => {
    e.preventDefault();
    const data = e.target.querySelector("textarea").value;
    try {
      await api.load(JSON.parse(data));
      alert("import finished");
    } catch (e) {
      alert("import failed: " + e);
    }
  };

  const [dump, setDump] = useState("");
  const getDump = async () => {
    const data = await api.dump();
    setDump(JSON.stringify(data, null, "  "));
  };

  return (
    <React.Fragment>
      <h2>Get data from the server</h2>
      <textarea value={dump} />
      <button onClick={getDump} type="button">
        Get data
      </button>

      <h2>Load data to the server</h2>
      <form onSubmit={handleSubmit}>
        <textarea />
        <button>Import</button>
      </form>
    </React.Fragment>
  );
}
