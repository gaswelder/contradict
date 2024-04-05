import React, { useState } from "react";
import { withRouter } from "react-router";
import { useAPI } from "./withAPI";

// Tells if an entry is empty.
const empty = (entry) => (entry.q + entry.a).trim() == "";

export const AddEntriesPage = withRouter(({ dictID }) => {
  const [state, _setState] = useState({
    loading: false,
    entries: [{ number: 0, q: "", a: "" }],
    nextEntryNumber: 1,
    lastResult: null,
  });
  const setState = (change) => {
    if (typeof change == "function") {
      _setState((x) => ({ ...x, ...change(x) }));
    } else {
      _setState((x) => ({ ...x, ...change }));
    }
  };
  const { loading, entries, lastResult } = state;
  const api = useAPI();

  const handleEntryInput = (number, field, event) => {
    const value = event.target.value;
    setState(function (state) {
      const entries = state.entries.slice();
      const pos = entries.findIndex((e) => e.number == number);
      entries[pos] = { ...entries[pos], [field]: value };
      return { entries };
    });
  };

  const addMore = () => {
    const freeRowsNumber = entries.filter(empty).length;
    if (freeRowsNumber > 0) return;
    setState(function (state) {
      const entry = { number: state.nextEntryNumber, q: "", a: "" };
      return {
        entries: [...state.entries, entry],
        nextEntryNumber: state.nextEntryNumber + 1,
      };
    });
  };

  const handlePaste = (event) => {
    const text = event.clipboardData.getData("text");

    const newEntries = text
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter((line) => line != "")
      .map((line, i) => {
        const [q, a] = line.split(" - ");
        return {
          number: state.nextEntryNumber + i + 1,
          q,
          a: a || "?",
        };
      });

    event.preventDefault();
    setState((state) => ({
      entries: [...state.entries, ...newEntries].filter(
        (r) => r.q != "" || r.a != ""
      ),
      nextEntryNumber: state.nextEntryNumber + newEntries.length + 1,
    }));
  };

  return (
    <form
      method="post"
      onSubmit={async (e) => {
        setState({ loading: true });
        e.preventDefault();
        const data = entries
          .filter((e) => !empty(e))
          .map(({ q, a }) => ({ q, a }));
        const { added, skipped } = await api.addEntries(dictID, data);
        setState({
          loading: false,
          entries: [{ number: 0, q: "", a: "" }],
          nextEntryNumber: 1,
          lastResult: { added, skipped },
        });
      }}
    >
      <p>
        <small>You can paste text lines in form &quot;q - a&quot;.</small>
      </p>
      {lastResult && (
        <p style={{ color: "rgb(51, 109, 221)" }}>
          {lastResult.added} added, {lastResult.skipped} skipped
        </p>
      )}
      {entries.map((entry) => (
        <div key={entry.number}>
          <input
            placeholder="Q"
            value={entry.q}
            onChange={(e) => handleEntryInput(entry.number, "q", e)}
            onBlur={addMore}
            onPaste={handlePaste}
          />
          <input
            placeholder="A"
            value={entry.a}
            onChange={(e) => handleEntryInput(entry.number, "a", e)}
            onBlur={addMore}
          />
        </div>
      ))}
      <div>
        <button disabled={loading}>Add</button>
      </div>
    </form>
  );
});
