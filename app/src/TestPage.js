import React from "react";
import Results from "./Results";
import Test from "./test";
import api from "./api";

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
    const questions = await api.test(this.props.match.params.id);
    this.setState({ questions });
  }

  async submit(entries) {
    const results = await api.submitAnswers(
      this.props.match.params.id,
      entries
    );
    this.setState({ results });
  }

  async reset() {
    this.setState({ questions: null, results: null });
    const questions = await api.test(this.props.match.params.id);
    this.setState({ questions });
  }

  render() {
    const { results, questions } = this.state;

    if (results) {
      return <Results data={results} onReset={this.reset} />;
    }
    if (questions) {
      return <Test data={questions} onSubmit={this.submit} />;
    }
    return "Loading...";
  }
}

export default TestPage;
