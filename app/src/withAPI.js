import React from "react";
import api from "./api";
import { withRouter } from "react-router";

function patch(api, onError) {
  const p = {};
  Object.keys(api).forEach(function(func) {
    p[func] = async function(...args) {
      try {
        return await api[func](...args);
      } catch (error) {
        onError(error);
        return error;
      }
    };
  });
  return p;
}

function withAPI(Component) {
  return withRouter(
    class withAPI extends React.Component {
      constructor(props) {
        super(props);
        this.state = {
          error: null
        };

        this.api = patch(api, error => {
          if (error.unauthorized) {
            this.props.history.push("/login");
            return;
          }
          this.setState({ error });
        });
      }

      render() {
        if (this.state.error) {
          return this.state.error.toString();
        }
        return <Component api={this.api} {...this.props} />;
      }
    }
  );
}

export default withAPI;
