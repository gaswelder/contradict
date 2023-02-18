import React from "react";
import api from "../api";

export default function DictPage(props) {
  const {
    match: {
      params: { id },
    },
  } = props;

  const [dict, setDict] = React.useState(null);
  React.useEffect(() => {
    api.dict(id).then(setDict);
  }, [id]);

  const [saving, setSaving] = React.useState(false);

  if (!dict) {
    return "...";
  }

  return (
    <form
      onSubmit={async (e) => {
        e.preventDefault();
        const name = e.target.querySelector('[name="name"]');
        const lookupURLTemplates = e.target.querySelector(
          '[name="lookupURLTemplates"]'
        );
        setSaving(true);
        await api.updateDict(dict.id, {
          name: name.value,
          lookupURLTemplates: lookupURLTemplates.value
            .split(/\n/)
            .map((line) => line.trim())
            .filter((line) => line != ""),
        });
        setSaving(false);
      }}
    >
      <div>
        <label>Name</label>
        <input name="name" defaultValue={dict.name} />
      </div>
      <div>
        <label>World lookup URL templates</label>
        <textarea
          name="lookupURLTemplates"
          defaultValue={dict.lookupURLTemplates.join("\n")}
        />
        <br />
        <small>
          For example, {"https://de.wiktionary.org/w/index.php?search={{word}}"}
        </small>
      </div>
      <div>
        <button type="submit" disabled={saving}>
          Save
        </button>
      </div>
    </form>
  );
}
