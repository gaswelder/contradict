import React from "react";
import { usePromise } from "./Resource";
import { useAPI } from "./withAPI";

export const DictPage = ({ dictID }) => {
  const { api, busy } = useAPI();
  const { data, error, loading } = usePromise(() => api.dict(dictID));
  if (loading) {
    return "Loading";
  }
  if (error) {
    return "Error: " + error.toString();
  }
  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        const name = e.target.querySelector('[name="name"]');
        const lookupURLTemplates = e.target.querySelector(
          '[name="lookupURLTemplates"]'
        );
        await api.updateDict(data.id, {
          name: name.value,
          lookupURLTemplates: lookupURLTemplates.value
            .split(/\n/)
            .map((line) => line.trim())
            .filter((line) => line != ""),
        });
      }}
    >
      <div>
        <label>Name</label>
        <input name="name" defaultValue={data.name} />
      </div>
      <div>
        <label>World lookup URL templates</label>
        <textarea
          name="lookupURLTemplates"
          defaultValue={data.lookupURLTemplates.join("\n")}
        />
        <br />
        <small>
          For example, {"https://de.wiktionary.org/w/index.php?search={{word}}"}
        </small>
      </div>
      <div>
        <button type="submit" disabled={busy}>
          Save
        </button>
      </div>
    </form>
  );
};
