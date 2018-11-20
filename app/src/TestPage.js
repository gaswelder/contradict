import React from "react";
import Results from "./Results";
import Test from "./test";
import withAPI from "./withAPI";

class TestPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      results: null,
      questions: null
    };
    this.submit = this.submit.bind(this);
    this.reset = this.reset.bind(this);
  }

  async componentDidMount() {
    const questions = await this.props.api.test(this.props.match.params.id);
    this.setState({ questions });
  }

  async submit(entries) {
    const id = this.props.match.params.id;
    const results = await this.props.api.submitAnswers(id, entries);
    this.setState({ results });
  }

  async reset() {
    this.setState({ questions: null, results: null });
    const questions = await this.props.api.test(this.props.match.params.id);
    this.setState({ questions });
  }

  render() {
    const { results, questions } = this.state;

    if (results) {
      return <Results data={results} onReset={this.reset} />;
    }
    if (questions) {
      return (
        <Test data={questions} onSubmit={this.submit} busy={this.props.busy} />
      );
    }
    return "Loading...";
  }
}

export default withAPI(TestPage);
