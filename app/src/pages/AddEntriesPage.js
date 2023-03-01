import React from "react";
import withAPI from "../components/withAPI";

// Tells if an entry is empty.
const empty = (entry) => (entry.q + entry.a).trim() == "";

class AddEntriesPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: false,
      entries: [{ number: 0, q: "", a: "" }],
      nextEntryNumber: 1,
      lastResult: null,
    };
    this.handleSubmit = this.handleSubmit.bind(this);
    this.addMore = this.addMore.bind(this);
    this.handlePaste = this.handlePaste.bind(this);
  }

  async handleSubmit(e) {
    const { api, dictID } = this.props;
    e.preventDefault();
    const data = this.state.entries
      .filter((e) => !empty(e))
      .map(({ q, a }) => ({ q, a }));
    this.setState({ loading: true });
    const { added, skipped } = await api.addEntries(dictID, data);
    this.setState({
      loading: false,
      entries: [{ number: 0, q: "", a: "" }],
      nextEntryNumber: 1,
      lastResult: { added, skipped },
    });
  }

  handleEntryInput(number, field, event) {
    const value = event.target.value;
    this.setState(function (state) {
      const entries = state.entries.slice();
      const pos = entries.findIndex((e) => e.number == number);
      entries[pos] = { ...entries[pos], [field]: value };
      return { entries };
    });
  }

  addMore() {
    const freeRowsNumber = this.state.entries.filter(empty).length;
    if (freeRowsNumber > 0) return;
    this.setState(function (state) {
      const entry = { number: state.nextEntryNumber, q: "", a: "" };
      return {
        entries: [...state.entries, entry],
        nextEntryNumber: state.nextEntryNumber + 1,
      };
    });
  }

  handlePaste(event) {
    const text = event.clipboardData.getData("text");
    const tuples = text
      .split(/\r?\n/)
      .map((line) => line.trim())
      .filter((line) => line != "")
      .map((line) => line.split(" - "));
    if (!tuples.every((tuple) => tuple.length == 2)) {
      return;
    }
    event.preventDefault();
    this.setState((state) => ({
      entries: [
        ...state.entries,
        ...tuples.map(([q, a], i) => ({
          number: state.nextEntryNumber + i + 1,
          q,
          a,
        })),
      ].filter((r) => r.q != "" || r.a != ""),
      nextEntryNumber: state.nextEntryNumber + tuples.length + 1,
    }));
  }

  render() {
    const { loading, entries, lastResult } = this.state;
    return (
      <form method="post" onSubmit={this.handleSubmit}>
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
              onChange={(e) => this.handleEntryInput(entry.number, "q", e)}
              onBlur={this.addMore}
              onPaste={this.handlePaste}
            />
            <input
              placeholder="A"
              value={entry.a}
              onChange={(e) => this.handleEntryInput(entry.number, "a", e)}
              onBlur={this.addMore}
            />
          </div>
        ))}
        <div>
          <button disabled={loading}>Add</button>
        </div>
      </form>
    );
  }
}

export default withAPI(AddEntriesPage);
