import React from "react";
import api from "./api";

class AddEntriesPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = { loading: false };
    this.handleSubmit = this.handleSubmit.bind(this);
  }

  async handleSubmit(e) {
    const dictID = this.props.match.params.id;
    e.preventDefault();
    const value = e.target.querySelector("textarea").value;
    this.setState({ loading: true });
    await api.addEntries(dictID, value);
    this.setState({ loading: false });
  }

  render() {
    const { loading } = this.state;
    return (
      <form method="post" onSubmit={this.handleSubmit}>
        <p>
          Input lines in the form <code>word - translation</code>
        </p>
        <div>
          <textarea name="words" cols="30" rows="10" disabled={loading} />
        </div>
        <div>
          <button disabled={loading}>Add</button>
        </div>
      </form>
    );
  }
}

export default AddEntriesPage;
